<?php

/*
** @author: Josef Harte
** @purpose: PHP for searching a patient's medical notes for matching text.
** Performs a fulltext search in natural language mode first. If this search returns empty, then a fulltext search in boolean mode is performed
** A FULLTEXT INDEX **must** exist on the note_text column. This can only be done using the InnoDB engine if using MySQL version 5.6.4 or higher. InnoDB is definitely preferred over MySIAM as InnoDB does not perform full table locks on write and supports transactions
*/

/* Database details */
$db_url = 'localhost';
$db_user = 'root';
$db_pwd = '1234';
$db = 'pomr';

$con = mysqli_connect( $db_url, $db_user, $db_pwd, $db );
if( !$con ) {
	error_log("Connection failed in note_search_ft.php");
	die();
}

/* SQL queries */
$sql_1 = <<<END
SELECT n.problem, n.note_text, n.doctor, n.entry_date FROM notes n JOIN problems p ON n.problem=p.description WHERE n.mrn=? AND p.status='{$_POST['search_type']}' AND MATCH(note_text) AGAINST(?)
END;

$sql_2 = <<<END
SELECT n.problem, n.note_text, n.doctor, n.entry_date FROM notes n JOIN problems p ON n.problem=p.description WHERE n.mrn=? AND p.status=('A' OR 'I') AND MATCH(note_text) AGAINST(?)
END;

/* Choose between queries */
$sql = NULL;
if( $_POST['search_type'] == 'AI' ) {
	$sql = $sql_2;
} else {
	$sql = $sql_1;
}

/* Submit query */
$stmt = mysqli_prepare( $con, $sql );
mysqli_stmt_bind_param( $stmt, 'is', $_POST['mrn'], $_POST['search_text'] );

$success = mysqli_stmt_execute( $stmt );

if( mysqli_stmt_num_rows($stmt) == 0 ) { // Then try fulltext search in boolean mode
	
	mysqli_stmt_close($stmt);

$sql_1 = <<<END
SELECT n.problem, n.note_text, n.doctor, n.entry_date FROM notes n JOIN problems p ON n.problem=p.description WHERE n.mrn=? AND p.status='{$_POST['search_type']}' AND MATCH(note_text) AGAINST(? IN BOOLEAN MODE)
END;

$sql_2 = <<<END
SELECT n.problem, n.note_text, n.doctor, n.entry_date FROM notes n JOIN problems p ON n.problem=p.description WHERE n.mrn=? AND p.status=('A' OR 'I') AND MATCH(note_text) AGAINST(? IN BOOLEAN MODE)
END;

/* Choose between queries */
$sql = NULL;
if( $_POST['search_type'] == 'AI' ) {
	$sql = $sql_2;
} else {
	$sql = $sql_1;
}
	/* Submit query */
	$stmt = mysqli_prepare( $con, $sql );
	mysqli_stmt_bind_param( $stmt, 'is', $_POST['mrn'], $_POST['search_text'] );
	$success = mysqli_stmt_execute( $stmt );
} 

	mysqli_bind_result( $stmt, $problem, $note_text, $doctor, $date );
	$notes = array();

	for( $i = 0; mysqli_stmt_fetch($stmt) == TRUE; ++$i ) {
		$notes[$i] = array( 'problem' => $problem, 'note' => $note_text, 'doctor' => $doctor, 'date' => $date );
	}

	/* Send back results */
	$json = json_encode($notes);
	echo $json;	
?>
