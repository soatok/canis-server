<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints\API;

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
        return $this->json([
            'test' => 'hello world',
            'test2' => get_class($this->splice('Accounts'))
        ]);
    }
}
