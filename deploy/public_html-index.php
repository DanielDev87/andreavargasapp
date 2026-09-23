<?php

/**
 * Entry point installed automatically as public_html/index.php by the deploy pipeline
 * (see .github/workflows/deploy.yml) — cPanel's main-domain docroot on Latinoamérica
 * Hosting is fixed at public_html and can't be repointed at the app's own public/
 * folder. No SSH is available on this plan either, so the pipeline builds a fresh
 * copy of public_html's contents in CI on every deploy (compiled assets change
 * filenames on every build anyway) and swaps __APP_PATH__ below for the real
 * absolute filesystem path to the app (the APP_ABSOLUTE_PATH secret) before
 * uploading it over FTP. Only the app root path differs from public/index.php.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appPath = '__APP_PATH__';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
