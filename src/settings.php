<?php
// Local settings (not committed to github)
if (file_exists(CANIS_ROOT . '/local/settings.php')) {
    $localSettings = require_once CANIS_ROOT . '/local/settings.php';
} else {
    $localSettings = [];
}
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

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
