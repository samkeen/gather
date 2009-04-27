<?php
/**
 * 
 * - Define directory context (where are we on the file system)
 * - Start a Logger
 * - define fine the ENV namespace
 */
//session_start();

/*
 * set up base context paths
 */
$PATH_APP_ROOT = dirname(dirname(__FILE__));
$PATH_RECEIVER_DIR = "{$PATH_APP_ROOT}/receiver";
$PATH_UTIL_DIR = "{$PATH_APP_ROOT}/util";
$PATH_LOGS_DIR = "{$PATH_APP_ROOT}/logs";

/*
 * turn on debugging (for logging)
 */
$DEBUG_ACTIVE = true;

/*
 * set up autoload
 * @see http://us3.php.net/autoload
 */
function __autoload($class) {
	global $PATH_RECEIVER_DIR;
    require $PATH_RECEIVER_DIR.'/' . str_replace('_', '/', $class) . '.php';
}

/*
 * strap a logger to the ENV namespace
 */
require $PATH_UTIL_DIR.'/Logger.php';
$logger = new Logger(Logger::DEBUG,"{$PATH_LOGS_DIR}/debug.log",$DEBUG_ACTIVE);
ENV::$log = $logger;

/**
 * Requirements check 
 */
if ($DEBUG_ACTIVE) {	
	// PHP > 5.2
	$php_version = explode('.',PHP_VERSION);
	if ($php_version[1]<2) {
		throw new Exception("Minimum supported PHP version is 5.2.x, version detected was [".PHP_VERSION
			."].  \nComment out 'if ($php_version[1]<2) {' in File".__FILE__."\n on or about line ".__LINE__." to try older versions of PHP");
	}
	// PDO
	
	// pgsql PDO driver
	if( ! in_array('pgsql',PDO::getAvailableDrivers())) {
		throw new Exception('PDO Driver for "pgsql" not found (was not returned in call to "PDO::getAvailableDrivers()")');
	}

}
/**
 * global namespace for holding utilities
 *
 */
class ENV {
    
    public static $log;
}

?>