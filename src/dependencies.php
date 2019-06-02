<?php

use Slim\App;
use Slim\Container;
use ParagonIE\CSPBuilder\CSPBuilder;
use ParagonIE\EasyDB\Factory;
use Soatok\Canis\Utility;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return function (App $app) {
    $container = $app->getContainer();

    $container['csp'] = function (Container $c) {
        return CSPBuilder::fromFile(__DIR__ . '/content_security_policy.json');
    };

    $container['db'] = function (Container $c) {
        $settings = $c->get('settings')['database'];
        return Factory::create(
            $settings['dsn'],
            $settings['username'] ?? '',
            $settings['password'] ?? '',
            $settings['options'] ?? []
        );
    };

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
