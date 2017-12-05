<?php

namespace AppBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 *
 */
class TokenAuthenticationException extends AuthenticationException
{

    function __construct($message)
    {
        $this->message = $message;
    }
}
