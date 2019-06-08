<?php
declare(strict_types=1);
namespace Soatok\Canis\Endpoints;

use Interop\Container\Exception\ContainerException;
use ParagonIE\HiddenString\HiddenString;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\Canis\Splices\Accounts;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

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
class Authorize extends Endpoint
{
    /** @var Accounts $accounts */
    protected $accounts;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->accounts = $this->splice('Accounts');
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
        if (empty($routerParams)) {
            // No params? No dice.
            return $this->redirect('/');
        }
        switch ($routerParams['action']) {
            case 'activate':
                return $this->activate($request, $routerParams);
            case 'login':
                return $this->login($request, $routerParams);
            case 'logout':
                return $this->logout($request, $routerParams);
            case 'register':
                return $this->register($request, $routerParams);
            case 'verify':
                return $this->verify($request, $routerParams);
            default:
                return $this->redirect('/');
        }
    }

    /**
     * @param RequestInterface $request
     * @param array $routerParams
     * @return ResponseInterface
     */
    protected function activate(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);
    }

    /**
     * @param RequestInterface $request
     * @param array $routerParams
     * @return ResponseInterface
     */
    protected function login(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);
    }

    /**
     * @param RequestInterface $request
     * @param array $routerParams
     * @return ResponseInterface
     */
    protected function logout(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);

    }

    /**
     * @param RequestInterface $request
     * @param array $routerParams
     * @return ResponseInterface
     */
    protected function register(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        // Do we have valid data?
        $post = $this->post($request);
        if (!empty($post)) {
            $userId = $this->accounts->createAccount(
                $post['username'],
                new HiddenString($post['password'])
            );
            if ($userId) {
                $this->view('register-success');
            }
        }
        return $this->view('register', ['post' => $post]);
    }

    /**
     * @param RequestInterface $request
     * @param array $routerParams
     * @return ResponseInterface
     */
    protected function verify(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);
    }

}
