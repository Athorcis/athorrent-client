<?php

define('ROOT_DIR', dirname(__DIR__));

define('APP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'app');
define('BIN_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'bin');
define('VAR_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'var');
define('VENDOR_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor');
define('WEB_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'web');

define('LOCALES_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'locales');

define('CACHE_DIR', VAR_DIR . DIRECTORY_SEPARATOR . 'cache');
define('TORRENTS_DIR', VAR_DIR . DIRECTORY_SEPARATOR . 'torrents');

if (function_exists('apcu_exists') && !DEBUG) {
    define('CACHE_TYPE', '\Athorrent\Utils\Cache\ApcCache');
} else {
    define('CACHE_TYPE', '\Athorrent\Utils\Cache\DummyCache');
}
