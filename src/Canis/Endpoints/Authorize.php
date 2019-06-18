<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\AnthroKit\Auth\Endpoints\Authorize as Base;
use Soatok\DholeCrypto\Exceptions\CryptoException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Authorize
 * @package Soatok\Canis\Endpoints
 *
 * Workflow:
 *
 * /auth/register -> create account
 * /auth/activate -> verify email address / etc.
 * /auth/login    -> login
 * /auth/verify   -> two-factor authentication prompt
 * /auth/logout   -> logout
 */
class Authorize extends Base
{

}
