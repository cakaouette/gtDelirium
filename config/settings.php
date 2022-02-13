<?php

$settings = [];
$settings['root'] = dirname(__DIR__);

// Error Handling Middleware settings
$settings['error'] = [

    // Should be set to false in production
    'display_error_details' => true,

    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests we also disable it
    'log_errors' => true,

    // Display error details in error log
    'log_error_details' => true,
];

$settings['logger'] = [
    'name' => 'app',
    'file' => __DIR__ . '/../logs/app.log',
    'level' => \Monolog\Logger::DEBUG,
    'file_permission' => 0775,
];

$settings['session'] = [
    'name' => 'app',
    'cache_expire' => 0,
];

$settings['salt'] = 'xxx';

$settings['db'] = [
    'path' => 'mysql:dbname=gtdelirium;host=db',
    'login' => 'gtdelirium',
    'pass' => 'gtdelirium'
];

if (file_exists(__DIR__ . '/../../env.php')) {
    require __DIR__ . '/../../env.php';
}
return $settings;