<?php

ob_start();

/**
 * Root project directory.
 */
if (!defined('APP_DIR')) {
	define('APP_DIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(__DIR__ . '/..')));
}

/**
 * Show debug info.
 */
if (!defined('APP_DEBUG')) {
	define('APP_DEBUG', $_SERVER['REMOTE_ADDR'] === '127.0.0.1');
}

@ini_set('display_errors', APP_DEBUG ? 1 : 0);
@ini_set('error_reporting', E_ALL | E_NOTICE | E_STRICT);

mb_internal_encoding('UTF-8');

require_once APP_DIR . '/lib/class/App.php';

/**
 * Prepares string for HTML output.
 *
 * @param string $string Unescaped string.
 * @return string Escaped string.
 */
function esc_html($string) {
	return htmlentities($string, ENT_COMPAT | ENT_QUOTES, mb_internal_encoding(), false);
}

/**
 * Prepares url for HTML output.
 *
 * @param string $url Unescaped url.
 * @return string Escaped url.
 */
function esc_url($url) {
	return esc_html(filter_var($url, FILTER_SANITIZE_URL));
}

App::init();