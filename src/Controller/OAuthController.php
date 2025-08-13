<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class OAuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher,
        private RequestStack $requestStack
    ) {}

    #[Route('/auth/google', name: 'oauth_google')]
    public function googleAuth(): Response
    {
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $redirectUri = 'http://localhost:8080/auth/google/callback';
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'openid email profile',
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        
        return $this->redirect($authUrl);
    }

    #[Route('/auth/google/callback', name: 'oauth_google_callback')]
    public function googleCallback(Request $request): Response
    {
        $code = $request->query->get('code');
        
        if (!$code) {
            $this->addFlash('error', 'Authorization code not received');
            return $this->redirectToRoute('login');
        }
        
        try {
            // Exchange code for access token
            $tokenData = $this->exchangeCodeForToken($code);
            
            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Failed to get access token');
            }
            
            // Get user info from Google
            $userInfo = $this->getGoogleUserInfo($tokenData['access_token']);
            
            // Find or create user
            $user = $this->findOrCreateUser($userInfo);
            
            // Authenticate user
            $this->authenticateUser($user);
            
            $this->addFlash('success', 'Successfully logged in with Google!');
            return $this->redirectToRoute('user_dashboard');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Google login failed: ' . $e->getMessage());
            return $this->redirectToRoute('login');
        }
    }

    #[Route('/auth/apple', name: 'oauth_apple')]
    public function appleAuth(): Response
    {
        // Apple OAuth implementation would go here
        return $this->render('oauth/demo.html.twig', [
            'provider' => 'Apple',
            'message' => 'Apple OAuth not yet implemented'
        ]);
    }

    #[Route('/auth/apple/callback', name: 'oauth_apple_callback')]
    public function appleCallback(Request $request): Response
    {
        // Apple OAuth callback implementation would go here
        return $this->render('oauth/demo.html.twig', [
            'provider' => 'Apple',
            'message' => 'Apple OAuth callback not yet implemented'
        ]);
    }

    #[Route('/auth/facebook', name: 'oauth_facebook')]
    public function facebookAuth(): Response
    {
        // Facebook OAuth implementation would go here
        return $this->render('oauth/demo.html.twig', [
            'provider' => 'Facebook',
            'message' => 'Facebook OAuth not yet implemented'
        ]);
    }

    #[Route('/auth/facebook/callback', name: 'oauth_facebook_callback')]
    public function facebookCallback(Request $request): Response
    {
        // Facebook OAuth callback implementation would go here
        return $this->render('oauth/demo.html.twig', [
            'provider' => 'Facebook',
            'message' => 'Facebook OAuth callback not yet implemented'
        ]);
    }

    private function exchangeCodeForToken(string $code): array
    {
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $redirectUri = 'http://localhost:8080/auth/google/callback';
        
        $postData = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('Token exchange failed with HTTP code: ' . $httpCode);
        }
        
        $tokenData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse token response');
        }
        
        return $tokenData;
    }

    private function getGoogleUserInfo(string $accessToken): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('Failed to get user info with HTTP code: ' . $httpCode);
        }
        
        $userInfo = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse user info response');
        }
        
        return $userInfo;
    }

    private function findOrCreateUser(array $userInfo): User
    {
        $email = $userInfo['email'];
        
        // Check if user already exists
        $user = $this->userRepository->findByEmail($email);
        
        if ($user) {
            // Update existing user's Google ID if not set
            if (!$user->getGoogleId()) {
                $user->setGoogleId($userInfo['id']);
                $user->setEmailVerified(true);
                $this->entityManager->flush();
            }
            return $user;
        }
        
        // Create new user
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($userInfo['given_name'] ?? '');
        $user->setLastName($userInfo['family_name'] ?? '');
        $user->setGoogleId($userInfo['id']);
        $user->setEmailVerified(true);
        $user->setProfilePicture($userInfo['picture'] ?? null);
        $user->setRoles(['ROLE_USER']);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function authenticateUser(User $user): void
    {
        // Create a simple authentication token
        // In a real application, you'd use Symfony's security system properly
        
        // For now, we'll store the user ID in the session
        $this->requestStack->getSession()->set('user_id', $user->getId());
        $this->requestStack->getSession()->set('user_email', $user->getEmail());
        $this->requestStack->getSession()->set('user_roles', $user->getRoles());
        
        // Update last login
        $user->setLastLoginAt(new \DateTime());
        $this->entityManager->flush();
    }
}
