<?php
final class ENV {
	
	public static $log;
    CONST DEBUG_ALWAYS = true;
	
	public static final function load_vendor_file($vendor_package_name) {
		$success = false;
		global $PATH__VENDOR_ROOT;
		$path_to_file = $PATH__VENDOR_ROOT.'/'.self::normalize_file_name($vendor_package_name);
		if (file_exists($path_to_file)) {
			ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.dirname($path_to_file));
			require $path_to_file;
			$success = true;
		}
		return $success;
	}
	/**
	 * all config files return an array called $config
	 */
	public static function load_config_file($config_file_name) {
		global $PATH__APP_ROOT;		
		include $PATH__APP_ROOT.'/config/'.self::normalize_file_name($config_file_name);
		return isset($config)?$config:null;
	}
	
	public static final function PATH($path, $append=null) {
		global $PATH__APP_ROOT,$PATH__FRAMWORK_ROOT;
		$append = ($append!=null) ? '/'.ltrim($append,'/') : '';
		if(substr($path,0,4)=='LIB_') {
			return $PATH__FRAMWORK_ROOT.CONSTS::$$path.$append;
		} else {
			return $PATH__APP_ROOT.CONSTS::$$path.$append;
		}	
	}
	public static final function get_controller_classname($requested_controller_name) {
		global $PATH__APP_ROOT, $logger;
		$controller_classname = null;
		$requested_controller_name = self::classifyName($requested_controller_name);
		$requested_controller_file = self::determine_app_or_framework_for_file(
                CONSTS::CONTROLLER_DIR.'/'.$requested_controller_name.'.php');
		if($requested_controller_file!==null) {
			$controller_classname = "Controller_".$requested_controller_name;
			require($requested_controller_file);
//			ini_set('include_path',$PATH__APP_ROOT
//				.'/'.CONSTS::CONTROLLER_DIR.$relative_path.PATH_SEPARATOR.ini_get('include_path'));
		} else {
			$logger->warn(__METHOD__.'  controller file requested ['.$PATH__APP_ROOT
				.'/'.CONSTS::CONTROLLER_DIR.'/'.$requested_controller_name.'.php'.'] does not exist');
		}
		return $controller_classname;
	}
	public static final function debug_active() {
		return (boolean)CONSTS::$DEBUG_ACTIVE;
	}
	/**
	 * This takes a relative path and first looks for it in app, then
	 * in lib.
	 * 
	 * @param string $relative_path string
	 * @return string
	 */
	public static function get_template_path($relative_path_to_file) {
		$ext_length = strlen(CONSTS::$TEMPLATE_FILE_EXT);
		$relative_path_to_file = substr($relative_path_to_file,-$ext_length,$ext_length)==CONSTS::$TEMPLATE_FILE_EXT
			? ltrim($relative_path_to_file,'/ ')
			: ltrim($relative_path_to_file,'/ ').CONSTS::$TEMPLATE_FILE_EXT;
		return self::determine_app_or_framework_for_file(CONSTS::$TEMPLATE_DIR.'/'.$relative_path_to_file); 
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public static final function FILE_NOT_FOUND_TEMPLATE() {
		return self::get_template_path(CONSTS::$FILE_NOT_FOUND_TEMPLATE);
	}
	/**
	 * given a relative path, this looks first for the file
	 * in APP then in FRAMEWORK
	 * 
	 * @param string $relative_path_to_file
	 * @return string The full path to the file if it exists on filesystem, else NULL
	 */
	private static final function determine_app_or_framework_for_file($relative_path_to_file) {
		global $PATH__APP_ROOT,$PATH__FRAMWORK_ROOT;
		$full_path_to_file = null;
		$relative_path_to_file = '/'.ltrim($relative_path_to_file,'/');
		if(file_exists($PATH__APP_ROOT.$relative_path_to_file)) {
			$full_path_to_file = $PATH__APP_ROOT.$relative_path_to_file;
		} else if(file_exists($PATH__FRAMWORK_ROOT.$relative_path_to_file)) {
			$full_path_to_file = $PATH__FRAMWORK_ROOT.$relative_path_to_file;
		}
		return $full_path_to_file;
	}
	public static final function classifyName($name) {
		$name = strtolower($name);
		if (strstr($name,'_')) {
			$name = explode('_',$name);
			$name = array_map('ucfirst',$name);
			$name = implode($name);
		} else {
			$name = ucfirst((strtolower($name)));
		}
		return $name;
		
	}
	/**
	 * filename will leave this method with NO leading slash and the correct 
	 * file extension.
	 * ex: given '/db_config'  will return 'db_config.php'
	 */
	private static function normalize_file_name($file_name, $ext_it_should_have='php') {
		$file_name = trim($file_name,' /');
		$ext_it_should_have = trim($ext_it_should_have,' .');
		return $file_name.(preg_match('/\.'.$ext_it_should_have.'$/i',$file_name)?'':'.'.$ext_it_should_have);
	}
}
?>