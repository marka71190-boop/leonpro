<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('leonpro');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

// Базовый путь сайта (работает и в корне домена, и в подпапке)
(function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $inAdmin = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')) === 'admin';
    $base = $inAdmin ? str_replace('\\', '/', dirname($scriptDir)) : $scriptDir;
    $base = rtrim($base, '/');
    define('BASE', $base === '.' ? '' : $base);
})();

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/layout.php';
