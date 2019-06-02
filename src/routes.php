<?php
namespace Soatok\Canis\Endpoints;

use Slim\App;
use Slim\Container;

return function (App $app) {
    /** @var Container $container */
    $container = $app->getContainer();

    $app->get('/', 'index');
    $app->get('', 'index');

    $container['index'] = function () use ($container) {
        return new Index($container);
    };
};
