<?php
namespace Soatok\Canis\Endpoints;

use Slim\App;
use Slim\Container;
use Soatok\Canis\Middleware\GuestsOnly;

return function (App $app) {
    /** @var Container $container */
    $container = $app->getContainer();
    $guestsOnly = new GuestsOnly($container);


    $app->get('/auth/{action:[^/]+}[/{extra:[^/]+}]', 'authorize');
    $app->get('/api', 'api-index');
    $app->get('/', 'index');
    $app->get('', 'index');

    $container['index'] = function () use ($container) {
        return new Index($container);
    };
    $container['api-index'] = function () use ($container) {
        return new API\Index($container);
    };
    $container['authorize'] = function () use ($container) {
        return new Authorize($container);
    };
};
