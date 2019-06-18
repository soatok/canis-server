<?php
use Soatok\AnthroKit\Auth\Fursona;
// Local settings (not committed to github)
if (file_exists(CANIS_ROOT . '/local/settings.php')) {
    $localSettings = require_once CANIS_ROOT . '/local/settings.php';
} else {
    $localSettings = [];
}
return [
    Fursona::CONTAINER_KEY => [
        'allow-twitter-auth' => false,
        'redirect' => [
            'auth-success' => '/',
            'activate-success' => '/',
            'empty-params' => '/',
            'invalid-action' => '/',
            'login' => '/auth/login',
            'register' => '/auth/register',
        ],
        'sql' => [
            'accounts' => [
                'table' => 'canis_accounts',
                'field' => [
                    'id' => 'accountid',
                    'login' => 'login',
                    'pwhash' => 'pwhash',
                    'twofactor' => 'twofactor',
                    'email' => 'email',
                    'email_activation' => 'email_activation',
                    'external_auth' => 'external_auth'
                ]
            ],
            'account_known_device' => [
                'table' => 'canis_account_known_device',
                'field' => [
                    'id' => 'deviceid',
                    'account' => 'accountid',
                    'created' => 'created',
                    'selector' => 'selector',
                    'validator' => 'validator'
                ]
            ]
        ],
        'templates' => [
            'email-activate' => 'email/activate.twig',
            'login' => 'login.twig',
            'register' => 'register.twig',
            'register-success' => 'register-success.twig',
            'two-factor' => 'two-factor.twig'
        ]
    ],
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        'password-key' => $localSettings['settings']['password-key']
                ??
            $keyring->load('symmetricuqQ7y5xejxCE7osGYD8UVej6r3OeIkGz8hPKxRn8E7Tq5HSePLYdCXS1luOiq18J'),

        'database' => $localSettings['settings']['database'] ?? [],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'twig' => [
            'template_paths' => [
                __DIR__ . '/../templates/'
            ],
        ],
    ],
];
