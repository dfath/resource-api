<?php

// src/AppBundle/Security/TokenAuthenticator.php
namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use AppBundle\Exception\TokenAuthenticationException;
use \Firebase\JWT\JWT;
use \Firebase\JWT\BeforeValidException;
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\SignatureInvalidException;
use \DomainException;
use \InvalidArgumentException;
use \UnexpectedValueException;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        if (!$request->headers->has('Authorization')) {
            return false;
        }
        $authorizationHeader = $request->headers->get('Authorization');

        $headerParts = explode(' ', $authorizationHeader);

        if (!(count($headerParts) === 2 && $headerParts[0] === 'Bearer')) {
            return false;
        }

        return array(
            'token' => $headerParts[1],
        );

    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $publicKey  = file_get_contents( __DIR__ . '/../../../public.key');

        $apiKey = $credentials['token'];

        try {
            $decoded = JWT::decode($apiKey, $publicKey, array('RS256'));
        } catch (UnexpectedValueException $e) {
            throw new TokenAuthenticationException($e->getMessage());
        } catch (SignatureInvalidException $e) {
            throw new TokenAuthenticationException($e->getMessage());
            return;
        } catch (BeforeValidException $e) {
            throw new TokenAuthenticationException($e->getMessage());
        } catch (ExpiredException $e) {
            throw new TokenAuthenticationException($e->getMessage());
        } catch (DomainException $e) {
            throw new TokenAuthenticationException($e->getMessage());
        }

        // if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($decoded->sub);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case
        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => $exception->getMessage()
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
