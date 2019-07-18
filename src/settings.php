<?php
use Soatok\AnthroKit\Auth\Fursona;
use Soatok\DholeCrypto\Keyring;

$keyring = new Keyring();
$anthrokit =[
    'allow-password-auth' => true,
    'allow-twitter-auth' => false,
    'chronicle' => [],
    'redirect' => [
        'account-banned' => '/generic-error/account-banned',
        'auth-success' => '/',
        'auth-failure' => '/generic-error/auth-failure',
        'activate-success' => '/',
        'empty-params' => '/generic-error/empty-params',
        'invalid-action' => '/generic-error/invalid-action',
        'invite-required' => '/generic-error/invite-required',
        'login' => '/auth/login',
        'logout-fail' => '/generic-error/logout-fail',
        'logout-success' => '/',
        'register' => '/auth/register',
        'twitter' => '/auth/twitter',
        'twitter-error' => '/generic-error/twitter-error',
        'two-factor' => '/auth/verify',
    ],
    'require-invite-register' => false,
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
        ],
        'invites' => [
            'table' => 'canis_invites',
            'field' => [
                'id' => 'inviteid',
                'from' => 'invitefrom',
                'twitter' => 'twitter',
                'email' => 'email',
                'invite_code' => 'invite_code',
                'claimed' => 'claimed',
                'created' => 'created',
                'newaccountid' => 'newaccountid'
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
];

$default = [
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
];


// Merge local changes to AnthroKit configuration
if (is_readable(CANIS_ROOT . '/local/anthrokit.php')) {
    $local = require_once CANIS_ROOT . '/local/anthrokit.php';
    $anthrokit = $local + $anthrokit;
}
$anthrokit = Fursona::autoConfig($anthrokit);

if (is_readable(CANIS_ROOT . '/local/settings.php')) {
    $local = require_once CANIS_ROOT . '/local/settings.php';
    return [
        Fursona::CONTAINER_KEY => $anthrokit,
        'settings' => $local + $default
    ];
}
return [
    Fursona::CONTAINER_KEY => $anthrokit,
    'settings' => $default
];
