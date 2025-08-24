<?php

namespace App\Service;

use App\Entity\OAuthCredential;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\OAuthCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_ConferenceData;
use Google_Service_Calendar_CreateConferenceRequest;
use Psr\Log\LoggerInterface;

class GoogleCalendarService
{
    private Google_Client $googleClient;
    private EntityManagerInterface $entityManager;
    private OAuthCredentialRepository $oauthCredentialRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        OAuthCredentialRepository $oauthCredentialRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->oauthCredentialRepository = $oauthCredentialRepository;
        $this->logger = $logger;
        
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient(): void
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
        $this->googleClient->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
        $this->googleClient->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
        $this->googleClient->setScopes([
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events'
        ]);
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setPrompt('consent');
    }

    public function createSessionEvent(Session $session): string
    {
        try {
            $tutor = $session->getTutor();
            if (!$tutor) {
                throw new \Exception('Session must have a tutor');
            }

            // Get or refresh OAuth credentials for the tutor
            $credentials = $this->getValidCredentials($tutor);
            if (!$credentials) {
                throw new \Exception('Tutor does not have valid Google OAuth credentials. Please connect your Google account.');
            }

            $this->googleClient->setAccessToken($credentials->getAccessToken());

            // Refresh token if needed
            if ($credentials->needsRefresh()) {
                $this->refreshToken($credentials);
            }

            $calendarService = new Google_Service_Calendar($this->googleClient);

            // Create the event
            $event = new Google_Service_Calendar_Event();
            $event->setSummary($session->getSessionTitle());
            $event->setDescription($session->getNotes() ?? 'Music lesson session');
            
            // Set start time
            $startDateTime = new Google_Service_Calendar_EventDateTime();
            $startDateTime->setDateTime($session->getStartAt()->format('c'));
            $startDateTime->setTimeZone($tutor->getTimezone() ?? 'UTC');
            $event->setStart($startDateTime);
            
            // Set end time
            $endDateTime = new Google_Service_Calendar_EventDateTime();
            $endDateTime->setDateTime($session->getEndAt()->format('c'));
            $endDateTime->setTimeZone($tutor->getTimezone() ?? 'UTC');
            $event->setEnd($endDateTime);

            // Add attendees (students)
            $attendees = [];
            foreach ($session->getEnrolledStudents() as $student) {
                $attendees[] = ['email' => $student->getEmail()];
            }
            if (!empty($attendees)) {
                $event->setAttendees($attendees);
            }

            // Create Google Meet conference
            $conferenceData = new Google_Service_Calendar_ConferenceData();
            $createRequest = new Google_Service_Calendar_CreateConferenceRequest();
            $createRequest->setRequestId(uniqid('meet_', true));
            $createRequest->setConferenceSolutionKey(['type' => 'hangoutsMeet']);
            $conferenceData->setCreateRequest($createRequest);
            $event->setConferenceData($conferenceData);

            // Insert the event
            $calendarId = $_ENV['GOOGLE_DEFAULT_CALENDAR_ID'] ?? 'primary';
            $createdEvent = $calendarService->events->insert($calendarId, $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all'
            ]);

            // Extract the Meet link
            $joinUrl = null;
            if ($createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
                foreach ($createdEvent->getConferenceData()->getEntryPoints() as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        $joinUrl = $entryPoint->getUri();
                        break;
                    }
                }
            }

            if (!$joinUrl) {
                throw new \Exception('Failed to generate Google Meet link');
            }

            // Update session with Google event ID and join URL
            $session->setGoogleEventId($createdEvent->getId());
            $session->setJoinUrl($joinUrl);
            $this->entityManager->flush();

            $this->logger->info('Google Calendar event created successfully', [
                'session_id' => $session->getId(),
                'event_id' => $createdEvent->getId(),
                'join_url' => $joinUrl
            ]);

            return $joinUrl;

        } catch (\Exception $e) {
            $this->logger->error('Failed to create Google Calendar event', [
                'session_id' => $session->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateSessionEvent(Session $session): string
    {
        if (!$session->getGoogleEventId()) {
            return $this->createSessionEvent($session);
        }

        try {
            $tutor = $session->getTutor();
            $credentials = $this->getValidCredentials($tutor);
            if (!$credentials) {
                throw new \Exception('Tutor does not have valid Google OAuth credentials');
            }

            $this->googleClient->setAccessToken($credentials->getAccessToken());

            if ($credentials->needsRefresh()) {
                $this->refreshToken($credentials);
            }

            $calendarService = new Google_Service_Calendar($this->googleClient);

            // Get existing event
            $calendarId = $_ENV['GOOGLE_DEFAULT_CALENDAR_ID'] ?? 'primary';
            $existingEvent = $calendarService->events->get($calendarId, $session->getGoogleEventId());

            // Update event details
            $existingEvent->setSummary($session->getSessionTitle());
            $existingEvent->setDescription($session->getNotes() ?? 'Music lesson session');
            
            $startDateTime = new Google_Service_Calendar_EventDateTime();
            $startDateTime->setDateTime($session->getStartAt()->format('c'));
            $startDateTime->setTimeZone($tutor->getTimezone() ?? 'UTC');
            $existingEvent->setStart($startDateTime);
            
            $endDateTime = new Google_Service_Calendar_EventDateTime();
            $endDateTime->setDateTime($session->getEndAt()->format('c'));
            $endDateTime->setTimeZone($tutor->getTimezone() ?? 'UTC');
            $existingEvent->setEnd($endDateTime);

            // Update attendees
            $attendees = [];
            foreach ($session->getEnrolledStudents() as $student) {
                $attendees[] = ['email' => $student->getEmail()];
            }
            $existingEvent->setAttendees($attendees);

            // Update the event
            $updatedEvent = $calendarService->events->update($calendarId, $session->getGoogleEventId(), $existingEvent, [
                'sendUpdates' => 'all'
            ]);

            // Extract the Meet link
            $joinUrl = null;
            if ($updatedEvent->getConferenceData() && $updatedEvent->getConferenceData()->getEntryPoints()) {
                foreach ($updatedEvent->getConferenceData()->getEntryPoints() as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        $joinUrl = $entryPoint->getUri();
                        break;
                    }
                }
            }

            if ($joinUrl) {
                $session->setJoinUrl($joinUrl);
                $this->entityManager->flush();
            }

            $this->logger->info('Google Calendar event updated successfully', [
                'session_id' => $session->getId(),
                'event_id' => $updatedEvent->getId()
            ]);

            return $joinUrl ?? $session->getJoinUrl();

        } catch (\Exception $e) {
            $this->logger->error('Failed to update Google Calendar event', [
                'session_id' => $session->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deleteSessionEvent(Session $session): void
    {
        if (!$session->getGoogleEventId()) {
            return;
        }

        try {
            $tutor = $session->getTutor();
            $credentials = $this->getValidCredentials($tutor);
            if (!$credentials) {
                $this->logger->warning('Cannot delete Google Calendar event - no valid credentials', [
                    'session_id' => $session->getId()
                ]);
                return;
            }

            $this->googleClient->setAccessToken($credentials->getAccessToken());

            if ($credentials->needsRefresh()) {
                $this->refreshToken($credentials);
            }

            $calendarService = new Google_Service_Calendar($this->googleClient);
            $calendarId = $_ENV['GOOGLE_DEFAULT_CALENDAR_ID'] ?? 'primary';
            
            $calendarService->events->delete($calendarId, $session->getGoogleEventId(), [
                'sendUpdates' => 'all'
            ]);

            // Clear session Google data
            $session->setGoogleEventId(null);
            $session->setJoinUrl(null);
            $this->entityManager->flush();

            $this->logger->info('Google Calendar event deleted successfully', [
                'session_id' => $session->getId()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to delete Google Calendar event', [
                'session_id' => $session->getId(),
                'error' => $e->getMessage()
            ]);
            // Don't throw - we don't want to fail session deletion if Google API fails
        }
    }

    private function getValidCredentials(User $user): ?OAuthCredential
    {
        return $this->oauthCredentialRepository->findValidCredentialsByUser($user, 'google');
    }

    private function refreshToken(OAuthCredential $credentials): void
    {
        try {
            $this->googleClient->refreshToken($credentials->getRefreshToken());
            $newToken = $this->googleClient->getAccessToken();

            $credentials->setAccessToken($newToken['access_token']);
            if (isset($newToken['expires_in'])) {
                $credentials->setExpiresAt(new \DateTime('+' . $newToken['expires_in'] . ' seconds'));
            }
            $credentials->setUpdatedAt(new \DateTime());

            $this->entityManager->flush();

            $this->logger->info('Google OAuth token refreshed successfully', [
                'user_id' => $credentials->getUser()->getId()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to refresh Google OAuth token', [
                'user_id' => $credentials->getUser()->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function testConnection(User $user): bool
    {
        try {
            $credentials = $this->getValidCredentials($user);
            if (!$credentials) {
                return false;
            }

            $this->googleClient->setAccessToken($credentials->getAccessToken());
            
            if ($credentials->needsRefresh()) {
                $this->refreshToken($credentials);
            }

            $calendarService = new Google_Service_Calendar($this->googleClient);
            $calendarId = $_ENV['GOOGLE_DEFAULT_CALENDAR_ID'] ?? 'primary';
            
            // Try to list calendars to test connection
            $calendarService->calendarList->listCalendarList(['maxResults' => 1]);
            
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Google Calendar connection test failed', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
