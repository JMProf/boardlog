<?php
declare(strict_types=1);

define('APP_NAME', 'BoardLog');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080');
define('TIMEZONE', 'Europe/Madrid');

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'boardlog');
define('DB_USER', getenv('DB_USER') ?: 'boardlog');
define('DB_PASS', getenv('DB_PASS') ?: 'boardlog');

define('SESSION_NAME', 'boardlog_sess');
define('CSRF_TOKEN_KEY', '_csrf');
define('COOKIE_SECURE', false);
define('COOKIE_SAMESITE', 'Lax');
define('COOKIE_HTTPONLY', true);

date_default_timezone_set(TIMEZONE);
