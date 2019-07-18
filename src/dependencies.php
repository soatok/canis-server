<?php

use Slim\App;
use Slim\Container;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\CSPBuilder\CSPBuilder;
use ParagonIE\EasyDB\Factory;
use ParagonIE\Quill\Quill;
use ParagonIE\Sapient\CryptographyKeys\{
    SigningPublicKey,
    SigningSecretKey
};
use Soatok\AnthroKit\Utility as AnthroKitUtil;
use Soatok\Canis\Utility;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Zend\Mail\Transport\{
    Sendmail,
    Smtp,
    SmtpOptions,
    TransportInterface
};

return function (App $app) {
    /** @var Container $container */
    $container = $app->getContainer();
    Utility::setContainer($container);

    $container['csp'] = function (Container $c): CSPBuilder {
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
    $container['logger'] = function (Container $c): \Monolog\Logger {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    $container['mailer'] = function (Container $c): TransportInterface {
        $settings = $c->get('settings')['email'] ?? [];
        if (empty($settings['transport'])) {
            $settings['transport'] = null;
        }
        switch ($settings['transport']) {
            case 'smtp':
                return new Smtp(
                    new SmtpOptions($settings['options'] ?? [])
                );
            default:
                return new Sendmail();
        }
    };

    $container['quill'] = function (Container $c) {
        $settings = $c->get('settings')['chronicle'];
        $publicKey = new SigningPublicKey(
            Base64UrlSafe::decode($settings['server-public-key'])
        );
        $secretKey = new SigningSecretKey(
            Base64UrlSafe::decode($settings['client-secret-key'])
        );
        return new Quill(
            $settings['url'],
            $settings['client-id'],
            $publicKey,
            $secretKey,
            AnthroKitUtil::getHttpClient(CANIS_ROOT . '/local/certs')
        );
    };

    $container['twig'] = function (Container $c): Environment {
        static $twig = null;
        if (!$twig) {
            $settings = $c->get('settings')['twig'];
            $loader = new FilesystemLoader($settings['template_paths']);
            $twig = Utility::terraform(new Environment($loader));
        }
        return $twig;
    };

    if (empty($_SESSION['anti-csrf'])) {
        $_SESSION['anti-csrf'] = random_bytes(33);
    }
};
