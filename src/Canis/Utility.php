<?php
declare(strict_types=1);
namespace Soatok\Canis;

use Interop\Container\Exception\ContainerException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\CSPBuilder\CSPBuilder;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Auth\Fursona;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Utility
 * @package Soatok\Canis
 */
abstract class Utility
{
    /** @var Container $container */
    private static $container;

    /**
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    public static function getResponseJson(ResponseInterface $response): array
    {
        $data = (string) $response->getBody();
        $decoded = json_decode($data, true);
        if (!is_array($decoded)) {
            return [];
        }
        return $decoded;
    }

    /**
     * Customize our Twig\Environment object
     *
     * @param Environment $env
     * @return Environment
     * @throws ContainerException
     */
    public static function terraform(Environment $env): Environment
    {
        $container = self::$container;

        /**
         * @twig-filter cachebust
         * Usage: {{ "/static/main.css"|cachebust }}
         */
        $env->addFunction(
            new TwigFunction(
                'authorized',
                function () {
                    return !empty($_SESSION['account_id']);
                }
            )
        );
        $env->addFilter(
            new TwigFilter(
                'cachebust',
                function (string $filePath): string {
                    $realpath = realpath(CANIS_PUBLIC . '/' . trim($filePath, '/'));
                    if (!is_string($realpath)) {
                        return $filePath . '?__404notfound';
                    }

                    $sha384 = hash_file('sha384', $realpath, true);

                    return $filePath . '?' . Base64UrlSafe::encode($sha384);
                }
            )
        );
        $env->addFunction(
            new TwigFunction(
                'anti_csrf',
                function () {
                    return '<input type="hidden" name="csrf-protect" value="' .
                        Base64UrlSafe::encode($_SESSION['anti-csrf']) .
                        '" />';
                },
                ['is_safe' => ['html']]
            )
        );
        $env->addFunction(
            new TwigFunction(
                'anti_csrf_ajax',
                function () {
                    return Base64UrlSafe::encode($_SESSION['anti-csrf']);
                },
                ['is_safe' => ['html', 'html_attr']]
            )
        );

        $env->addFunction(
            new TwigFunction(
                'csp_nonce',
                function (string $directive = 'script-src') use ($container) {
                    /** @var CSPBuilder $csp */
                    $csp = Utility::$container['csp'];
                    return $csp->nonce($directive);
                }
            )
        );

        $env->addFunction(
            new TwigFunction(
                'clear_message_once',
                function () {
                    $_SESSION['message_once'] = [];
                }
            )
        );

        $env->addFilter(new TwigFilter('ucfirst', 'ucfirst'));

        $settings = $container['settings']['twig-custom'] ?? [];
        $env->addGlobal('canis_custom', $settings);
        $env->addGlobal('theme_id', null);
        $env->addGlobal('anthrokit', $container->get(Fursona::CONTAINER_KEY));
        $env->addGlobal('canis_settings', $container->get('settings'));

        $env->addGlobal('session', $_SESSION);

        return $env;
    }
}
