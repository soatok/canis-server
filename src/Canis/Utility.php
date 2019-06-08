<?php
declare(strict_types=1);
namespace Soatok\Canis;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\CSPBuilder\CSPBuilder;
use Slim\Container;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Utility
 * @package Soatok\Canis
 */
abstract class Utility
{
    private static $container;

    /**
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * Customize our Twig\Environment object
     *
     * @param Environment $env
     * @return Environment
     */
    public static function terraform(Environment $env): Environment
    {
        $container = self::$container;

        /**
         * @twig-filter cachebust
         * Usage: {{ "/static/main.css"|cachebust }}
         */
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
                'csp_nonce',
                function (string $directive = 'script-src') use ($container) {
                    /** @var CSPBuilder $csp */
                    $csp = $container->get('csp');
                    return $csp->nonce($directive);
                }
            )
        );

        return $env;
    }
}
