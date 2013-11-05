/* 
** @author: Josef Harte
** @purpose: JavaScript for patient_search.html. Submits search fields to PHP file and populates table with results
** Uses jQuery
*/

/* Attach AJAX call to search form's submit button */
$('#patient_search').submit( function(event) {
	event.preventDefault();	
	
	/* Clear patient table of existing data and say "Searching...". Patient rows have class=table_row */
	$('.table_row').remove();
	$('#table_body').prepend(
		'<tr class="table_row"><td></td><td></td><td></td><td></td>' +
		'<td>Searching...</td></tr>'
	);
	
	var formData = $(this).serialize();
	
	/* Sends an AJAX POST request so use $_POST in PHP file */
	$.post('patient_search.php', formData, patientResults);
	
	function patientResults(data, status) {
		/* PHP sends back an array (an array when it's parsed here)! */
		var results = jQuery.parseJSON(data);
		
		/* Check if any results were found */
		$('.table_row').remove();
		if( results.length == 0 ) {
			$('#table_body').prepend(
				'<tr class="table_row"><td></td><td></td><td></td><td></td>' +
				'<td>No matching patients found</td></tr>'
			);
		} else {
			for( var i = 0; i < results.length; i++) {			
				$('#table_body').prepend(
					"<tr class='table_row'><td class='results_mrn'>" + results[i].mrn + '</td>' +
					'<td>' + results[i].firstname + '</td>' +
					'<td>' + results[i].lastname + '</td>' +
					'<td>' + results[i].dob + '</td>' +
					'<td>' + results[i].address + '</td></tr>'
				);
			}
		}
		
		/* Select patient in table on click and send their MRN number to summary page.
		Function is attached to the new table rows */
		$('#table_body tr').click( function(event) {
			var mrn = $(this).find('td.results_mrn').html();
			window.location = '../summary/summary_page.php?mrn=' + mrn;
		});			
	};
});







