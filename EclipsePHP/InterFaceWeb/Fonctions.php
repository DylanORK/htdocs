<?php
function connectMaBase(){
    $base = mysql_connect ('localhost:81', 'root', 'TheEvilBrooD666');  
    
    return $base ;
}

function escape_data ($data) {

	if (function_exists('mysql_real_escape_string')) {
		global $dbc; // Need the connection.
		$data = mysql_real_escape_string (trim($data), $dbc);
		$data = strip_tags($data);
	} else {
		$data = mysql_escape_string (trim($data));
		$data = strip_tags($data);
	}
	return $data;

}

?>