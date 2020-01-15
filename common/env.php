<?php
/**
 * Require helpers
 */
require_once(__DIR__ . '/helpers.php');

/**
 * Load application environment from .env file
 */
$dotenv = new \Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();

/**
 * Init application constants
 */
defined('YII_DEBUG') or define('YII_DEBUG', (in_array(env('SERVER_ROLE'), ['dev', 'sprint'])));
/**
 *
 */
defined('YII_GII') or define('YII_GII', (env('SERVER_ROLE') == 'dev') ? true : false);
/**
 *
 */
defined('YII_ENV') or define('YII_ENV', env('SERVER_ROLE', 'prod'));
/**
 *
 */
defined('YII_START') or define('YII_START', time());
