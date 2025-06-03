<?php

use App\core\Flash;
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/functions.php';
$config = require __DIR__ . '/../config/config.php';

session_start();

use App\core\Database;
use App\core\Installer;

// ---- SETUP/INSTALL LOGIC ----

define('INSTALL_FLAG', __DIR__ . '/../storage/installed.txt');
$installFile = __DIR__ . '/../database/db.sql'; // path to your .sql schema file

function app_needs_install()
{
    // 1. Check flag file first
    if (file_exists(INSTALL_FLAG))
        return false;
    // 2. Check if DB table exists
    try {
        $pdo = Database::getInstance()->pdo();
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        return false;
    } catch (PDOException $e) {
        return true;
    }
}

if (app_needs_install()) {
    if (!file_exists($installFile)) {
        http_response_code(500);

        render('error', [
            'errorTitle' => 'Install Error',
            'errorMessage' => 'Could not find database install file: <code>$installFile.'
        ], null);
        exit();
    }
    try {
        $installer = new Installer(Database::getInstance()->pdo(), INSTALL_FLAG, $installFile);
        $installer->install();
        Flash::set('success', 'Application installed successfully!');
    } catch (Exception $e) {
        http_response_code(500);

        render('error', [
            'errorTitle' => 'Install Error',
            'errorMessage' => '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
        ], null);
        exit();
    }
}

// ---- APP STARTS AS NORMAL BELOW ----

use App\core\Router;

$router = new Router([
    __DIR__ . '/../routes/web.php',
    [__DIR__ . '/../routes/api.php', '/api'],
]);
$router->dispatch();
