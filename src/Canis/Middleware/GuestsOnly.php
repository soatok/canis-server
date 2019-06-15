<?php
declare(strict_types=1);
namespace Soatok\Canis\Middleware;

use Psr\Http\Message\{
    MessageInterface,
    RequestInterface,
    ResponseInterface
};
use Soatok\AnthroKit\Middleware;

/**
 * Class GuestsOnly
 * @package Soatok\Canis\Middleware
 */
class GuestsOnly extends Middleware
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): MessageInterface {
        if (!empty($_SESSION['account_id'])) {
            header('Location: /');
            exit;
        }
        return $next($request, $response);
    }
}
