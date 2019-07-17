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

    $app->group('/manage', function() use ($app, $container) {

    })->add($authOnly);
    // Only authenticated users can logout:
    $app->any('/auth/{action:logout}[/{extra:[^/]+}]', 'authorize')
        ->add($authOnly);
    // Only guests can do this:
    $app->any('/auth/{action:register|invite|login|twitter|verify}[/{extra:[^/]+}]', 'authorize')
        ->add($guestsOnly);
    // No middleware on activation:
    $app->any('/auth/{action:activate}[/{extra:[^/]+}]', 'authorize');
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
