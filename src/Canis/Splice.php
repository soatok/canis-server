<?php
declare(strict_types=1);
namespace Soatok\Canis;

use ParagonIE\EasyDB\EasyDB;
use Slim\Container;
use Interop\Container\Exception\ContainerException;

/**
 * Class Splice
 * @package Soatok\Canis
 */
class Splice
{
    /** @var EasyDB $db */
    protected $db;

    /**
     * Splice constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->db = $container->get('db');
    }
}
