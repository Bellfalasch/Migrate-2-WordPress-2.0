// On page load run jQuery scripts
$(function() {

	// Project page
	//////////////////////////////////////////////////////////////
	$("body.migrate-project .btn-danger").click( function() {
		
		var answer = confirm("Are you sure you want to delete this entire project and all of its content? This action can't be undone!");

		return answer;

	});

	$("body.migrate-project .btn-warning").click( function() {
		
		var answer = confirm("Are you sure you want to remove the contents (only crawled and washed data) of this project? This action can't be undone!");

		return answer;

	});

	
	// "Manage"
	//////////////////////////////////////////////////////////////
	$("body.migrate-step3 .btn-danger").click( function() {
		
		var answer = confirm("Are you sure you want to delete this entire page and all of its content? This action can't be undone!");

		return answer;

	});

	
	// "Finalize"
	//////////////////////////////////////////////////////////////
	$("body.migrate-step7 .btn-danger").click( function() {
		
		var answer = confirm("Are you sure you want to delete this entire page and all of its content? This action can't be undone!");

		return answer;

	});

	// When the modal opens, set the title to the right one
	$('#html-modal').on('show', function() {
		$('#html-modal h3').text("Edit page HTML");
	});

	$("body.migrate-step7 #html-modal .btn-primary").click( function() {

		alert("Saving changes ...");

	});

});