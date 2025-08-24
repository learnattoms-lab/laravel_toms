<?php

namespace App\Controller\Admin;

use App\Service\GoogleCalendarService;
use App\Service\AzureBlobStorageService;
use App\Repository\OAuthCredentialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/api-health')]
#[IsGranted('ROLE_ADMIN')]
class ApiHealthController extends AbstractController
{
    public function __construct(
        private GoogleCalendarService $googleCalendarService,
        private AzureBlobStorageService $azureBlobStorageService,
        private OAuthCredentialRepository $oauthCredentialRepository
    ) {}

    #[Route('/', name: 'admin_api_health', methods: ['GET'])]
    public function index(): Response
    {
        $healthStatus = [
            'google_calendar' => $this->checkGoogleCalendarHealth(),
            'azure_blob_storage' => $this->checkAzureBlobStorageHealth(),
            'oauth_credentials' => $this->checkOAuthCredentialsHealth(),
        ];

        return $this->render('admin/api_health.html.twig', [
            'health_status' => $healthStatus,
            'page_title' => 'API Health Status'
        ]);
    }

    private function checkGoogleCalendarHealth(): array
    {
        $status = [
            'status' => 'unknown',
            'message' => '',
            'details' => []
        ];

        try {
            // Check if Google OAuth credentials are configured
            if (empty($_ENV['GOOGLE_CLIENT_ID']) || empty($_ENV['GOOGLE_CLIENT_SECRET'])) {
                $status['status'] = 'error';
                $status['message'] = 'Google OAuth credentials not configured';
                return $status;
            }

            // Check if there are any valid OAuth credentials
            $validCredentials = $this->oauthCredentialRepository->findValidCredentials('google');
            
            if (empty($validCredentials)) {
                $status['status'] = 'warning';
                $status['message'] = 'No valid Google OAuth credentials found';
                $status['details'][] = 'Users need to authorize Google Calendar access';
                return $status;
            }

            // Test connection with first valid credential
            $firstCredential = $validCredentials[0];
            $testResult = $this->googleCalendarService->testConnection($firstCredential->getUser());
            
            if ($testResult) {
                $status['status'] = 'success';
                $status['message'] = 'Google Calendar API connection successful';
                $status['details'][] = 'Connected as: ' . $firstCredential->getUser()->getEmail();
                $status['details'][] = 'Credentials valid until: ' . $firstCredential->getExpiresAt()->format('Y-m-d H:i:s');
            } else {
                $status['status'] = 'error';
                $status['message'] = 'Google Calendar API connection failed';
                $status['details'][] = 'Unable to connect with valid credentials';
            }

        } catch (\Exception $e) {
            $status['status'] = 'error';
            $status['message'] = 'Google Calendar API check failed: ' . $e->getMessage();
        }

        return $status;
    }

    private function checkAzureBlobStorageHealth(): array
    {
        $status = [
            'status' => 'unknown',
            'message' => '',
            'details' => []
        ];

        try {
            // Check if Azure configuration is present
            if (empty($_ENV['AZURE_BLOB_CONNECTION_STRING']) || 
                empty($_ENV['AZURE_BLOB_CONTAINER']) || 
                empty($_ENV['AZURE_BLOB_PUBLIC_BASE'])) {
                $status['status'] = 'error';
                $status['message'] = 'Azure Blob Storage configuration incomplete';
                $status['details'][] = 'Missing required environment variables';
                return $status;
            }

            // Test connection
            $testResult = $this->azureBlobStorageService->testConnection();
            
            if ($testResult) {
                $status['status'] = 'success';
                $status['message'] = 'Azure Blob Storage connection successful';
                $status['details'][] = 'Container: ' . $_ENV['AZURE_BLOB_CONTAINER'];
                $status['details'][] = 'Base URL: ' . $_ENV['AZURE_BLOB_PUBLIC_BASE'];
            } else {
                $status['status'] = 'error';
                $status['message'] = 'Azure Blob Storage connection failed';
                $status['details'][] = 'Unable to connect to storage account';
            }

        } catch (\Exception $e) {
            $status['status'] = 'error';
            $status['message'] = 'Azure Blob Storage check failed: ' . $e->getMessage();
        }

        return $status;
    }

    private function checkOAuthCredentialsHealth(): array
    {
        $status = [
            'status' => 'unknown',
            'message' => '',
            'details' => []
        ];

        try {
            // Get all OAuth credentials
            $allCredentials = $this->oauthCredentialRepository->findAll();
            $validCredentials = $this->oauthCredentialRepository->findValidCredentials('google');
            $expiredCredentials = $this->oauthCredentialRepository->findExpiredCredentials('google');

            $status['details'][] = 'Total credentials: ' . count($allCredentials);
            $status['details'][] = 'Valid credentials: ' . count($validCredentials);
            $status['details'][] = 'Expired credentials: ' . count($expiredCredentials);

            if (count($validCredentials) > 0) {
                $status['status'] = 'success';
                $status['message'] = 'OAuth credentials available';
                
                // Show details for each valid credential
                foreach ($validCredentials as $credential) {
                    $user = $credential->getUser();
                    $expiresAt = $credential->getExpiresAt();
                    $status['details'][] = sprintf(
                        'User: %s (expires: %s)',
                        $user->getEmail(),
                        $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'Unknown'
                    );
                }
            } elseif (count($allCredentials) > 0) {
                $status['status'] = 'warning';
                $status['message'] = 'All OAuth credentials are expired';
                $status['details'][] = 'Users need to re-authorize Google access';
            } else {
                $status['status'] = 'error';
                $status['message'] = 'No OAuth credentials found';
                $status['details'][] = 'No users have authorized Google Calendar access';
            }

        } catch (\Exception $e) {
            $status['status'] = 'error';
            $status['message'] = 'OAuth credentials check failed: ' . $e->getMessage();
        }

        return $status;
    }
}
