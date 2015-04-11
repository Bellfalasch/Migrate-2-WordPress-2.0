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

	// Ajax-save the forms data
	$('#ajax-form').on('submit', function(e) {

		e.preventDefault();

		ajaxform = $('#ajax-form');
		post_to = ajaxform.attr("action");
		message = ajaxform.find(".hidden");

		message.show();
		message.addClass("text-info");
		message.text("Saving ...");

//		alert( ajaxform.attr("action") );

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

	});

});