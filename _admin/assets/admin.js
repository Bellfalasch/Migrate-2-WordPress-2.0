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


	$('#html-modal').on('show', function() {

		//alert("yolo1");
		console.log("Modal activated");
		//console.log( $('#ajax-form') );

		$('#html-modal').on('shown', function() {

			console.log("Modal done (shown)");
			//console.log( $('#ajax-form') );

			// Ajax-save the forms data
			$('#ajax-form').on('submit', function(e) {

				e.preventDefault();
				//alert("yolo2");
				//return false;

				ajaxform = $('#ajax-form');
				post_to = ajaxform.attr("action");
				message = ajaxform.find(".hidden");

				message.show();
				message.removeClass("text-success");
				message.removeClass("text-error");
				message.addClass("text-info");
				message.text("Saving ...");

		//		alert( ajaxform.attr("action") );
				console.log( $('#ajax-form').serialize() );

				$.post(
					post_to,
					$('#ajax-form').serialize()
				)
				.done(function() {
					message.removeClass("text-info");
					message.addClass("text-success");
					message.text("Saved!");
					setTimeout( function() {
						message.hide();
						message.removeClass("text-success");
					}, 2500);
				})
				.fail(function() {
					message.removeClass("text-info");
					message.addClass("text-error");
					message.text("NOT saved!");
					setTimeout( function() {
						message.hide();
						message.removeClass("text-error");
					}, 2500);
				});

				//return false;

			});

		});

	});

	// Destroy the contents of the modal when it closes so we can use it again on another page
	// Why: - http://stackoverflow.com/questions/12286332/twitter-bootstrap-remote-modal-shows-same-content-everytime
	$('#html-modal').on('hide', function () {

		console.log("Modal hiding");

		$form = $('#ajax-form');

		$('#html-modal').on('hidden', function () {

			console.log("Modal hidden");

			//$form.remove();
			//$(this).removeData('#html-modal');
			//$('#html-modal').removeData('#html-modal');
			$('#html-modal').removeData(); // Correct way of doing it

			console.log("Modal destroyed");

		});
	});

});