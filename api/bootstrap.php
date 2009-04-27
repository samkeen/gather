<?php

session_start();

$PATH__WEB_ROOT = dirname(dirname(__FILE__));
$PATH__APP_ROOT = $PATH__WEB_ROOT.'/app';
$PATH__FRAMWORK_ROOT = $PATH__WEB_ROOT.'/aabot';
$PATH__VENDOR_ROOT = $PATH__WEB_ROOT.'/vendor';

require $PATH__FRAMWORK_ROOT.'/utils.php';
require $PATH__FRAMWORK_ROOT.'/ENV.php';
// include custom routes file which should put $custom_routes in scope;
include 'config/custom_routes.php';

final class CONSTS {
	CONST CONTROLLER_DIR = 'Controller';
	CONST MODEL_DIR = 'Model';
	CONST VIEW_DIR = 'View';
	
	// default app settings
	public static $DEBUG_ACTIVE = true;
	
	public static $DEFAULT_CONTROLLER = 'Default';
	public static $DEFAULT_TEMPLATE = 'default';
	public static $DEFAULT_LAYOUT = 'default';
	
	public static $DEFAULT_ACTION = 'index';
	public static $FILE_NOT_FOUND_ACTION = 'file_not_found_action';
	
	public static $APP_DIR = '/';
	public static $LAYOUT_DIR = '/View/layout';
	public static $TEMPLATE_DIR = '/View/templates';
	
	public static $TEMPLATE_FILE_EXT = '.php';
	
	public static $LIB_LAYOUT_DIR = '/View/layout';
	public static $LIB_TEMPLATE_DIR = '/View/templates';
	
	public static $FILE_NOT_FOUND_TEMPLATE = '/default/file_not_found.php';
	
	public static $RESPONSE_GLOBAL_DEFAULT = 'html';
	public static $RESPONSE_HTML = 'html';
	public static $RESPONSE_JSON = 'json';
	public static $RESPONSE_TEXT = 'txt';
}

?>