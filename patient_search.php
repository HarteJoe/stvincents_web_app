<?php

/*
** @author: Josef Harte
** @purpose: PHP for finding patients in MySQL database using data submitted from patient_search.html.
** Results are sent back in JSON format
*/

/* Database details */
$db_url = 'localhost';
$db_user = 'root';
$db_pwd = '1234';
$db = 'pomr';

/* Form data and fields */
$formData = array($_POST['status'], $_POST['mrn'], $_POST['firstname'], $_POST['lastname'], $_POST['dob'], $_POST['address']);
$formFields = array('status', 'mrn', 'first_name', 'last_name', 'date_of_birth', 'address');

/* Build the SQL search query based on what form fields are not empty */
$sql = NULL;
if( $_POST['status'] != 'A' ) {
	$sql = "SELECT mrn, first_name, last_name, DATE_FORMAT(date_of_birth,'%d-%m-%Y'), address FROM patients WHERE status='{$_POST['status']}' AND";
} else {
	$sql = "SELECT mrn, first_name, last_name, DATE_FORMAT(date_of_birth,'%d-%m-%Y'), address FROM patients WHERE (status='I' OR status='O') AND";
}

$size = sizeof($formData);

/* This loop checks if form fields are empty or not. It adds non empty ones
to the query string. It takes care to put the ANDs in the right place.
It makes sure the end of the query string has no AND and puts a ; at the end.*/

for( $i = 1; $i < $size; $i++ ) {
	if( $i != $size - 1 ) {
		if( !empty($formData[$i]) ) {
			$sql = $sql . " {$formFields[$i]} LIKE \"{$formData[$i]}%\" AND"; 
		}
	} else {
		if( !empty($formData[$i]) ) {
			if( $formFields[$i] == 'address' ) {
				$sql = $sql . " {$formFields[$i]} LIKE \"%{$formData[$i]}%\";"; 
			} else {
				$sql = $sql . " {$formFields[$i]} LIKE \"{$formData[$i]}%\";"; 
			}
		}
		else {
			$sql = substr($sql, 0, strlen($sql)-4 ) . ";"; 
		}
	}
}

/* Connect and submit query */
$con = mysqli_connect($db_url, $db_user, $db_pwd, $db);
if(!$con) {
	error_log('Connection to database failed!');
}
$result = mysqli_query($con, $sql);
if(!$result) {
	error_log('SQL query failed!');
}

/* Fetch and process rows from results. Each row is an array in the array $resultsArray */
$resultsArray = array();
for ($i = 0; $i < mysqli_num_rows($result); $i++) {
	$row = mysqli_fetch_row($result);
	$resultsArray[$i] = array( 'mrn' => $row[0], 'firstname' => $row[1], 'lastname' => $row[2], 'dob' => $row[3], 'address' => $row[4]);
}

/* Send back JSON data to JavaScript file */
$data = json_encode($resultsArray);
echo $data;

?>
