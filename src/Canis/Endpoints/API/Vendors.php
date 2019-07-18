<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints\API;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;

/**
 * Class Vendors
 * @package Soatok\Canis\Endpoints\API
 */
class Vendors extends Endpoint
{
    /** @var \Soatok\Canis\Splices\Vendors $vendors */
    protected $vendors;

    /**
     * Vendors constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->vendors = $this->splice('Vendors');
    }

    protected function create(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json([]);
    }

    protected function index(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json([]);
    }

    protected function viewVendor(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json([]);
    }

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
        $action = $routerParams['action'] ?? 'index';
        switch ($action) {
            case 'create':
                return $this->create($request, $routerParams);
            case 'view':
                return $this->viewVendor($request, $routerParams);
            case 'index':
                return $this->index($request, $routerParams);
            default:
                return $this->redirect('/api/vendors');
        }
    }
}