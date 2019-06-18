<?php
namespace Soatok\Canis\Endpoints;

use Slim\App;
use Slim\Container;
use Soatok\AnthroKit\Auth\Middleware\{
    AuthorizedUsersOnly,
    GuestsOnly
};

return function (App $app) {
    /** @var Container $container */
    $container = $app->getContainer();
    $guestsOnly = new GuestsOnly($container);
    $authOnly = new AuthorizedUsersOnly($container);

    $app->any('/auth/{action:[^/]+}[/{extra:[^/]+}]', 'authorize');
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
