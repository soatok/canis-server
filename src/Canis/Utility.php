<?php
declare(strict_types=1);
namespace Soatok\Canis;

use ParagonIE\ConstantTime\Base64UrlSafe;
use Twig\Environment;
use Twig\TwigFilter;

/**
 * Class Utility
 * @package Soatok\Canis
 */
abstract class Utility
{
    /**
     * Customize our Twig\Environment object
     *
     * @param Environment $env
     * @return Environment
     */
    static public function terraform(Environment $env): Environment
    {
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

        return $env;
    }
}
