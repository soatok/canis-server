<?php
declare(strict_types=1);
namespace Soatok\Canis;

use ParagonIE\CSPBuilder\CSPBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Slim\Http\Stream;
use Twig\Environment;

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

    /**
     * Endpoint constructor.
     * @param Container $container
     * @throws \Interop\Container\Exception\ContainerException
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
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
