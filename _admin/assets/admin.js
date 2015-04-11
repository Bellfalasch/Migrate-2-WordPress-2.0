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
	ajaxform = $('#ajax-form');
	message = ajaxform.find(".hidden");

	ajaxform.on('submit', function(e){
		e.defaultPrevent;
		$.post(
			window.location.href,
			ajaxform.serialize()
		)
			.done(function() {
				//alert( "second success" );
				message.removeClass("text-info");
				message.show();
				message.addClass("text-success");
				message.text("Saved!");
				setTimeout( function() {
					//message.css("opacity","0");
					message.hide();
					message.removeClass("text-success");
				}, 2500);
			})
			.fail(function() {
				//alert( "error" );
				message.removeClass("text-info");
				message.show();
				message.addClass("text-error");
				message.text("NOT saved!");
				setTimeout( function() {
					//message.css("opacity","0");
					message.hide();
					message.removeClass("text-error");
				}, 2500);
			})
			.always(function() {
				//alert( "finished" );
				message.show();
				message.addClass("text-info");
				message.text("Saving");
			})
		;
	});

});