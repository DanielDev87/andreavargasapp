<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

/**
 * Post-deploy hook for shared hosting without SSH/CLI access.
 *
 * FTP can upload files but can't run `php artisan migrate`, clear caches, or restart
 * the queue worker — this script does that over HTTP instead, called by the GitHub
 * Actions deploy workflow right after it finishes uploading the new build.
 *
 * Installed automatically as public_html/deploy-hook.php by the pipeline (see
 * .github/workflows/deploy.yml), generated from this template with __APP_PATH__ and
 * __PUBLIC_HTML_PATH__ swapped for the real absolute filesystem paths (the app lives
 * outside public_html — see deploy/public_html-index.php for why).
 *
 * Protected by a long random token (DEPLOY_HOOK_TOKEN, set in the app's real .env,
 * NOT in public_html) compared with hash_equals() to avoid timing attacks. Treat the
 * token as a secret: anyone who has it can force a redeploy-time run of
 * migrations/cache-clear/queue-restart.
 */
$appPath = '__APP_PATH__';
$publicHtmlPath = '__PUBLIC_HTML_PATH__';

require $appPath.'/vendor/autoload.php';

$app = require_once $appPath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

header('Content-Type: application/json');

$expectedToken = env('DEPLOY_HOOK_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (empty($expectedToken) || ! hash_equals((string) $expectedToken, (string) $providedToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Invalid token']);
    exit;
}

$steps = [];

try {
    // `php artisan storage:link` would create the symlink inside $appPath/public/,
    // which isn't what's actually served (public_html is, and it's a separate copy —
    // see deploy/public_html-index.php). Without this, anything uploaded AFTER a
    // deploy (course images, generated certificates) would never become visible, since
    // FTP-copying files only captures a snapshot at deploy time. Idempotent: only
    // creates it once, first deploy onward.
    $storageLink = $publicHtmlPath.'/storage';
    if (! file_exists($storageLink)) {
        symlink($appPath.'/storage/app/public', $storageLink);
        $steps['storage:link'] = 'created';
    } else {
        $steps['storage:link'] = 'already exists';
    }

    Artisan::call('migrate', ['--force' => true]);
    $steps['migrate'] = trim(Artisan::output());

    Artisan::call('config:cache');
    $steps['config:cache'] = 'ok';

    Artisan::call('route:cache');
    $steps['route:cache'] = 'ok';

    Artisan::call('view:cache');
    $steps['view:cache'] = 'ok';

    // No hay worker persistente sin SSH (ver deploy/crontab-queue-worker.txt) — el cron
    // ya recoge el código nuevo en cada corrida, pero esto no hace daño de todas formas.
    Artisan::call('queue:restart');
    $steps['queue:restart'] = 'ok';

    echo json_encode(['ok' => true, 'steps' => $steps]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => $e->getMessage(), 'steps' => $steps]);
}
