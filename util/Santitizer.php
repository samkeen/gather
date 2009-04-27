<?php
class Util_Sanitizer {
	
	private static $sanitation_rules = array(
		'receive_rfid' =>
			array(
			    'scan_time'	=> FILTER_SANITIZE_STRING,
				'rfid'   	=> FILTER_SANITIZE_STRING,
			//    'rfid'    => array('filter'    => FILTER_SANITIZE_STRING,
			//                       'flags'     => FILTER_REQUIRE_ARRAY, 
			//                       'options'   => array('min_range' => 1, 'max_range' => 10)
			//                           ),
			    'reader_id'	=> FILTER_SANITIZE_STRING
			)
	);
	private static $input_sources = array('POST'=>INPUT_POST, 'GET'=>INPUT_GET);
	
	public function sanitize_input_data($data_source, $rule_name) {
		$sanitized_data = null;
		$data_source = isset(self::$input_sources[strtoupper($data_source)])?self::$input_sources[strtoupper($data_source)]:null;
		if( $data_source && isset(self::$sanitation_rules[$rule_name])) {
			$sanitized_data = filter_input_array($data_source, self::$sanitation_rules[$rule_name]);
		}
		return $sanitized_data;
	}
}
?>