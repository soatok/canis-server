<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\Canis\Endpoint;

/**
 * Class Index
 * @package Soatok\Canis\Endpoints
 */
class Index extends Endpoint
{
    public function __invoke(RequestInterface $request): ResponseInterface
    {
        $this->cspBuilder->addSource('script-src', 'https://twitch.tv');
        return $this->view('index.twig');
    }
}
