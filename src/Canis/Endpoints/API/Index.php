<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints\API;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\AnthroKit\Endpoint;

/**
 * Class Index
 * @package Soatok\Canis\Endpoints
 */
class Index extends Endpoint
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json([

        ]);
    }
}
