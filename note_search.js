/*
** @author: Josef Harte
** @purpose: JavaScript for searching a particular patient's medical notes for text and displaying results
** Uses jQuery
*/

/* Dialog for inputting "Search All Notes" text */
$( function() {
	$('#search_notes_input').dialog({
		autoOpen: false,
		show: {
		effect: "blind",
		duration: 1000
		},
		modal: true,
		hide: {
			effect: "explode",
			duration: 1000
		},
		height: 200,
		width: 500
	});
	
	/* Make the "Search" div a button and open dialog on click */
	$( "div.search_button" ).button().click(function() {		
		$( "#search_notes_input" ).dialog( "open" );
	});
});

/* Dialog for displaying all found notes that match text */
$( function() {
	$('#found_notes').dialog({
		autoOpen: false,
		show: {
		effect: "blind",
		duration: 1000
		},
		hide: {
			effect: "explode",
			duration: 1000
		},
		height: 650,
		width: 800,
	});
});

/* Submit the search text when click "Search" button inside the dialog */
$('#submit_search_button').click( function (event) {
	event.preventDefault();
	var searchText = $('#search_text').val();	
	var searchType = $('#search_type').val();	
	var query = location.search.split('=');
	var urlMrn = query[1];
	formData = { mrn: urlMRN, search_text: searchText, search_type: searchType };
	console.log(formData);
	$.post('note_search_ft.php', formData, getMatchedNotes);
	$('#search_notes_input').dialog('close');	
});

/* Populate results table with matching notes' details */
$( function getMatchedNotes(data, status) {

	$('#found_notes_table_body tr').remove();
	var notes = jQuery.parseJSON(data);
	console.log(notes);
	$('#found_notes').dialog('open');
	
	for( var i = 0; i < notes.length; i++) {
		$('#found_notes_table_body').prepend(
			'<tr><td class="note_preview">' + notes[i].note.substring(0,20) + '...' + '</td>'+
			'<td class="note_preview">' + notes[i].problem + '</td>'+
			'<td class="note_preview">' + notes[i].date + '</td>'+
			'<td class="note_preview">' + notes[i].doctor + '</td>'+
			'<td class="note_preview"><div class="arrow"></div></td></tr>' +
			'<tr><td colspan="4">' + notes[i].note + '</td>' +
			'<td><div class="arrow"></div></td></tr>'
		);
	}
	
	$("#found_notes_table tr:odd").addClass("odd");
    $("#found_notes_table tr:not(.odd)").hide();
    $("#found_notes_table tr:first-child").show();
	
	$("#found_notes_table_body tr.odd").click( function(){
    		$(this).next("tr").toggle();
        $(this).find(".arrow").toggleClass("up");
    });
});
