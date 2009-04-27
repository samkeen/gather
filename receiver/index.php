<?php
/**
 * Front Controller to recieive RFID transmissions from in the field
 * AP Sensors.
 * This is expecting a GET request with the parameters noted in the 
 * $sanitizers array below.
 * Those parameters are santitized and passed on the the processing 
 * portion of the application.
 */

/*
 * fitler definition to cleanse the GET request
 * @see www.php.net/filter
 */
$sanitizers = array(
    'scan_time'	=> FILTER_SANITIZE_STRING,
	'rfid'   	=> FILTER_SANITIZE_STRING,
    'reader_id'	=> FILTER_SANITIZE_STRING
);
/*
 * apply the sanitation on the GET request
 */
$sanitized['get'] = filter_input_array(INPUT_GET, $sanitizers);
/*
 * Look for the echo param and simply echo back if found
 */
if (isset($_GET['echo'])) {
	echo '<pre>';
	var_dump($sanitized['get']);
	echo '</pre>';
}
/*
 * Look for man (manual) GET parameter
 */
if (isset($_GET['man'])) { ?>
<pre>
Acceptable GET parameters
	- [scan_time] 	: the time at which the rfid tag was scanned
	- [rfid] 	: the id of the tag that was read
	- [reader_id] 	: the id of the reader that recorded the rfid scan
	- [echo] 	: just echo back what is received in GET (after being sanitized) (for debugging)
	- [man] 	: show this information
</pre>
<?php } 

?>