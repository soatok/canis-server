<?php
declare(strict_types=1);
namespace Soatok\Canis;

use Interop\Container\Exception\ContainerException;
use ParagonIE\CSPBuilder\CSPBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Slim\Http\Stream;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Endpoint
 * @package Soatok\Canis
 */
abstract class Endpoint
{
    /** @var Container $container */
    protected $container;

    /** @var CSPBuilder $cspBuilder */
    protected $cspBuilder;

    /** @var array<string, Splice> $splices */
    protected $splices = [];

    /**
     * Endpoint constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->cspBuilder = $this->container->get('csp');
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function json(
        array $data,
        int $status = StatusCode::HTTP_OK,
        array $headers = []
    ): Response {
        $headers['Content-Type'] = 'application/json';
        return $this->respond(
            json_encode($data, JSON_PRETTY_PRINT),
            $status,
            $headers
        );
    }

    /**
     * @param string $file
     * @param array $args
     * @return string
     *
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(string $file, array $args = []): string
    {
        /** @var Environment $twig */
        $twig = $this->container->get('twig');
        return $twig->render($file, $args);
    }

    /**
     * Synthesize an HTTP response
     *
     * @param string $body
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function respond(
        string $body,
        int $status = StatusCode::HTTP_OK,
        array $headers = []
    ): Response {
        return $this->cspBuilder->injectCSPHeader(
            new Response(
                $status,
                new Headers($headers),
                $this->stream($body)
            )
        );
    }

    /**
     * @param string $name
     * @return Splice
     */
    public function splice(string $name): Splice
    {
        if (!isset($this->splices[$name])) {
            $className = 'Soatok\\Canis\\Splices\\' . $name;
            $this->splices[$name] = new $className($this->container);
        }
        return $this->splices[$name];
    }

    /**
     * Create Stream object from string
     *
     * @param string $input
     * @return Stream
     */
    public function stream(string $input)
    {
        $fp = fopen('php://temp', 'wb');
        fwrite($fp, $input);
        return new Stream($fp);
    }

    /**
     * @param string $file
     * @param array $args
     * @param int $status
     * @param array $headers
     * @return Response
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function view(
        string $file,
        array $args = [],
        int $status = StatusCode::HTTP_OK,
        array $headers = []
    ): Response {
        $headers['Content-Type'] = 'text/html';
        return $this->respond(
            $this->render($file, $args),
            $status,
            $headers
        );
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public abstract function __invoke(RequestInterface $request): ResponseInterface;
}
