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

	// Leaving focus on a field should Ajax-post it and save the changes
	$("body.migrate-step3 table input").blur( function() {

		// Extract all sent data and split it up to be sent with our post request
		var elem = $(this);
		var orgvalue = elem.attr("data-original-value");
		var value = elem.val();

		// Only continue if we actually changed anything in the field
		if ( orgvalue != value ) {

			var name = elem.attr("name");

			var split = name.split('_');
			var id = split[0];
			var type = split[1]; // slug or page

			// Secure/validate data
			if ( type == "slug" ) {
				type = "slug";
			} else {
				type = "title";

				// Title is about to change, fetch the slug field too because the database will update that too (automatically)
				var sibling = elem.parent().siblings("td").children("input");
			}

			var container = elem.parent();

			$.ajax({
				type: "POST",
				url: $(".input_ajaxurl").val(), // A hidden input field from the calling html page (needed some PHP parsing of the URL)
				data: {
					"id": id,
					"type": type,
					"value": value
				},
				success: function(slug) {
					//console.log("Field name: " + name);
					//console.log("Page ID: " + id);
					//console.log("Change what: " + type);
					//console.log("To: " + value);

					elem.attr("data-original-value", value); // Update the original-value data attribute

					// We update a title, so also the slug will need to be updated (sent from the server)
					if ( sibling ) {
						sibling.attr("data-original-value", slug);
						sibling.val(slug);
					}
					container.addClass("success");
					setTimeout( function() { container.removeClass("success"); }, 1000);
				},
				error: function() {
					container.addClass("error");
					setTimeout( function() { container.removeClass("error"); }, 1000);
				}
			});

		}

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