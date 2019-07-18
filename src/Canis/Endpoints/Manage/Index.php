<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints\Manage;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\AnthroKit\Endpoint;

/**
 * Class Index
 * @package Soatok\Canis\Endpoints\Manage
 */
class Index extends Endpoint
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        return $this->view('manage/index.twig');
    }
}
