<?php
// ** Database settings ** //
/* The name of the database for Allerta-vvf */
define('DB_NAME', '@@db@@');

/* Database username */
define('DB_USER', '@@user@@');

/* Database password */
define('DB_PASSWORD', '@@password@@');

/* Database hostname */
define('DB_HOST', '@@host@@');

/* Database hostname */
define('DB_PREFIX', '@@prefix@@');

/* JWT Keys */
define('JWT_PUBLIC_KEY', '@@public_key@@');
define('JWT_PRIVATE_KEY', '@@private_key@@');

/* Telegram bot options */
define('BOT_TELEGRAM_API_KEY', '');
define('BOT_TELEGRAM_USERNAME', '');
define('BOT_TELEGRAM_DEBUG_USER', null);

/* Sentry options */
define('SENTRY_CSP_REPORT_URI', '');
define('SENTRY_ENABLED', false);
define('SENTRY_DSN', '');
define('SENTRY_ENV', 'prod');

//define('BASE_PATH', 'allerta/');