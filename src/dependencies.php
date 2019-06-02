<?php

use Slim\App;
use Slim\Container;
use Soatok\Canis\Utility;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return function (App $app) {
    $container = $app->getContainer();

    // monolog
    $container['logger'] = function (Container $c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    $container['twig'] = function (Container $c): Environment {
        $settings = $c->get('settings')['twig'];
        $loader = new FilesystemLoader($settings['template_paths']);
        $twig = new Environment($loader);
        return Utility::terraform($twig);
    };
};
