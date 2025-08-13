<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OAuthAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function supports(Request $request): ?bool
    {
        // Check if this is an OAuth authenticated request
        $session = $request->getSession();
        $hasOAuth = $session->has('oauth_authenticated') && $session->get('oauth_authenticated') === true;
        
        // Debug output
        error_log('OAuthAuthenticator::supports called. Has OAuth: ' . ($hasOAuth ? 'true' : 'false'));
        error_log('Session data: ' . json_encode([
            'oauth_authenticated' => $session->get('oauth_authenticated'),
            'oauth_user_id' => $session->get('oauth_user_id'),
            'oauth_user_email' => $session->get('oauth_user_email')
        ]));
        
        return $hasOAuth;
    }

    public function authenticate(Request $request): Passport
    {
        $session = $request->getSession();
        $userId = $session->get('oauth_user_id');
        
        if (!$userId) {
            throw new AuthenticationException('No OAuth user found in session');
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new AuthenticationException('OAuth user not found in database');
        }

        return new Passport(
            new UserBadge($user->getEmail(), function($userIdentifier) use ($user) {
                return $user;
            }),
            new CustomCredentials(function($credentials, User $user) {
                // OAuth users are pre-authenticated
                return true;
            }, 'oauth')
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Clear OAuth session data after successful authentication
        $session = $request->getSession();
        $session->remove('oauth_user_id');
        $session->remove('oauth_user_email');
        $session->remove('oauth_user_roles');
        $session->remove('oauth_authenticated');
        $session->remove('current_user');

        if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Clear OAuth session data on failure
        $session = $request->getSession();
        $session->remove('oauth_user_id');
        $session->remove('oauth_user_email');
        $session->remove('oauth_user_roles');
        $session->remove('oauth_authenticated');
        $session->remove('current_user');

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
