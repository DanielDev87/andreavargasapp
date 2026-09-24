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

// storage/app/public/**, storage/framework/{cache,sessions,views}/** and
// storage/logs/** are all deliberately excluded from the FTP upload — they hold
// either user-uploaded content or per-deploy runtime state, and must never get
// overwritten by a deploy. The side effect: none of these folders exist yet after the
// very first deploy, since FTP only creates directories it's actually uploading files
// into. This has to run BEFORE Laravel boots below, not after: config/view.php's
// 'compiled' path is resolved via realpath() once, during boot — creating the folder
// afterwards wouldn't fix an already-cached-as-false value for this same request.
// @-suppressed: only errors if a folder already exists, which is fine.
foreach ([
    'storage/app/public',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
] as $relative) {
    $dir = $appPath.'/'.$relative;
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

require $appPath.'/vendor/autoload.php';

$app = require_once $appPath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

header('Content-Type: application/json');

$expectedToken = config('services.deploy_hook_token');
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
    // FTP-copying files only captures a snapshot at deploy time.
    $storageLink = $publicHtmlPath.'/storage';
    $storageTarget = $appPath.'/storage/app/public';

    // is_link() (unlike file_exists()) still returns true for a symlink whose target
    // doesn't exist yet — without this check, a symlink created before its target
    // existed would look "missing" on every later run and fail on re-creation attempts.
    if (is_link($storageLink) || file_exists($storageLink)) {
        $steps['storage:link'] = 'already exists';
    } else {
        symlink($storageTarget, $storageLink);
        $steps['storage:link'] = 'created';
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
