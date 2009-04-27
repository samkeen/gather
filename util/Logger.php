<?php
/**
 * Very simple Logging class.  Allowed you to add debugging statments in code and
 * "turn them off" in production
 * 
 * any of the logging methods can be used as a check to see if that level is enabled.
 * Usefull to save the work of building complex log entries when that level is not 
 * even enabled.  ex:
 * <code>
 * if($logger->debug()) {
 * 		$logger->debug(__METHOD__. 'User:'.get_user($id).' was not found in primary env: '
 * 			.get_primary_env($request). ' or in secondary ' . get_secondaty_env());
 * }
 * </code> 
 * 
 * @author "Sam Keen" <sam@pageindigo.com>
 * 
 */
class Logger {
	
	private $current_log_level;
	const DEBUG = 40;
	const NOTICE = 30;
	const WARN = 20;
	const ERROR = 10;
	/*
	 * this is used to start log statements with for instance: "[DEBUG]..."
	 */
	private $error_type_human = array(
		self::DEBUG => "DEBUG",
		self::NOTICE => "NOTICE",
		self::WARN => "WARN",
		self::ERROR => "ERROR"
	);
	
	private $maintain_buffer_of_statements = false;
	private $buffered_statements = array();
	/*
	 * hold the optional full path to the log file.  If not defined, we log to 
	 * the PHP defined system log using error_log();
	 */
	private $log_file_path;
	/**
	 * @param $log_level The level at which to log.
	 * @param $log_file_path If given we will attempt to write
	 * log statments to that file, if not given we will write to the system log
	 * $maintain_buffer_of_statements If true all log statments are save in a buffer that
	 * can be recalled with $this->buffered_statements()
	 */
	public function __construct($log_level, $log_file_path=null, $maintain_buffer_of_statements=false) {
		$this->log_file_path = $log_file_path;
		$this->current_log_level = $log_level;
		$this->maintain_buffer_of_statements = $maintain_buffer_of_statements;
	}

	/**
	 * Log a message at debug level
	 * @param $log_message The message to log
	 * If $log_message is null, then the caller of the method is just
	 * checking if this log level is active (reason for the return value)
	 */
	public function debug($log_message=null) {
		return $this->log_for_level(self::DEBUG, $log_message);
	}
	/**
	 * Log a message at info level
	 * @param $log_message The message to log
	 * If $log_message is null, then the caller of the method is just
	 * checking if this log level is active (reason for the return value)
	 */
	public function notice($log_message=null) {
		return $this->log_for_level(self::NOTICE, $log_message);
	}
	/**
	 * Log a message at info level
	 * @param $log_message The message to log
	 * If $log_message is null, then the caller of the method is just
	 * checking if this log level is active (reason for the return value)
	 */
	public function warn($log_message=null) {
		return $this->log_for_level(self::WARN, $log_message);
	}
	/**
	 * Log a message at error level
	 * @param $log_message The message to log
	 * If $log_message is null, then the caller of the method is just
	 * checking if this log level is active (reason for the return value)
	 */
	public function error($log_message=null) {
		return $this->log_for_level(self::ERROR, $log_message);
	}
	public function buffered_statements() {
		return $this->buffered_statements;
	}
	/**
	 * Log a message at error level
	 * @param $level The level at which to attempt to log
	 * @param $log_message The message to log
	 * If $log_message is null, then the caller of the method is just
	 * checking if this log level is active (reason for the return value)
	 */
	private function log_for_level($level, $log_message) {
		if ($this->current_log_level >= $level && $log_message!==null) {
			$this->logMessage($log_message, $level);
		}
		return $this->current_log_level >= $level; 
	}
	/**
	 * log message if we are set at or above the $log_level supplied
	 */
	private function logMessage($log_message, $log_level) {
		$message = "[".$this->error_type_human[$log_level]."] ".$log_message;
		if ($this->log_file_path===NULL) {
			$this->writeSystemMessage($message);
		} else {
			$this->writeFileMessage($message);
		}
		if ($this->maintain_buffer_of_statements) {
			$this->buffered_statements[] = $message;
		}
	}
	private function writeSystemMessage($log_message) {
		error_log($log_message);
	}
	private function writeFileMessage($log_message) {
		if (is_writable($this->log_file_path)) {
			if (!$handle = fopen($this->log_file_path, 'a')) {
				$error = "[".__METHOD__."] CANNOT OPEN FILE ($this->log_file_path)";
				error_log('+!+!+!+!+!+'.$error.' TO WRITE MESSAGE: '. $log_message);
			}
			// Write $somecontent to our opened file.
			if (fwrite($handle, $log_message."\n") === FALSE) {
				$error = "[".__METHOD__."] CANNOT WRITE TO FILE ($this->log_file_path)";
				error_log('+!+!+!+!+!+'.$error.' TO WRITE MESSAGE: '. $log_message);
			}
			fclose($handle);
		} else {
			error_log("+!+!+!+!+!+[".__METHOD__."] LOG FILE ({$this->log_file_path}) NOT WRITABLE!!! ");
		}
	}
}