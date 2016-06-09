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
/*
	$("body.migrate-step3 .btn-danger").click( function() {

		var answer = confirm("Are you sure you want to delete this entire page and all of its content? This action can't be undone!");

		return answer;

	});
*/
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
				url: $(".input_ajaxurl_title").val(), // A hidden input field from the calling html page (needed some PHP parsing of the URL)
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

	// Delete pages
	$("body.migrate-step3 table button.delete").click( function() {
		ajax_delete_page( $(this) );
	});

	function ajax_delete_page(elem) {
		// Extract all sent data and split it up to be sent with our post request
		var del_id = elem.attr("data-delete-id"); // Page to "delete"
		var do_del = elem.attr("data-delete"); // Delete or undo the delete? true/false

		// Secure/validate data
		if ( do_del == "true" ) {
			do_del = "true";
		} else {
			do_del = "false";
		}

		var container = elem.parent().parent(); // The wrapping tr

		$.ajax({
			type: "POST",
			url: $(".input_ajaxurl_del").val(), // A hidden input field from the calling html page (needed some PHP parsing of the URL)
			data: {
				"id": del_id,
				"delete": do_del
			},
			success: function(html) {
				console.log("Page ID: " + del_id);
				console.log("delete: " + do_del);

				if ( do_del === "true" ) {
					container.addClass("hidden"); // Hide row with data
					// Add undo row and attach this same click event to the undo button
					var new_row = $("<tr class='deleted'><td colspan='6'>Page deleted! <button class='btn btn-mini btn-primary' data-delete-id='" + del_id + "' data-delete='false'>Undo this?</button></td></tr>");
					container.after(new_row);
					var new_btn = new_row.find("td button");
					//new_btn.click( ajax_delete_page( new_btn ) ); // This way will start an eternal loop
					//new_btn.bind('click', { param: new_btn }, ajax_delete_page); // This didn't work
					new_btn.on( 'click', function() { // .on is the new jQuery way after .live and others
						ajax_delete_page( $(this) );
					});
					console.log( new_row );
					console.log( new_btn );
				} else {
					container.prev().removeClass("hidden");
					container.remove(); // Destroy the undo-row
				}

			},
			error: function(html) {
				container.addClass("error");
				setTimeout( function() { container.removeClass("error"); }, 1000);
				alert(html);
			}
		});	
	}

	// Make a page into the child of any selected parent page
	$("body.migrate-step3 table button.addChild").click( function() {
		ajax_makePageIntoChild( $(this) );
	});
	

	function ajax_makePageIntoChild(elem) {
		// Extract all sent data and split it up to be sent with our post request
		var parent_id = elem.attr("data-makeparent-parent");
		var child_id = elem.attr("data-makeparent-child");
		var undo = elem.attr("data-makeparent-undo");

		// Secure/validate data
		if ( undo == "false" ) {
			undo = "false";
		} else {
			undo = "true";
		}
		var container = elem.parent().parent(); // The wrapping tr

		console.log($(".input_ajaxurl_child").val());

		$.ajax({
			type: "POST",
			url: $(".input_ajaxurl_child").val(), // A hidden input field from the calling html page (needed some PHP parsing of the URL)
			data: {
				"parent": parent_id,
				"child": child_id,
				"undo": undo
			},
			success: function(html) {
				console.log("parent_id: " + parent_id);
				console.log("child_id: " + child_id);

				if ( undo === "false" ) {
					container.addClass("child");
					// Toggle button behaviour now that it is a child
					elem.text("Undo");
					elem.attr("data-makeparent-undo", "true");
				} else {
					container.removeClass("child");
					if (parent_id > 0) {
						elem.text("Add child");
						elem.attr("data-makeparent-undo", "false");
					} else {
						elem.hide();
					}
				}

			},
			error: function(html) {
				container.addClass("error");
				setTimeout( function() { container.removeClass("error"); }, 1000);
				alert(html);
			}
		});
	}


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

	// Make table sortable
	if ( $("body.migrate-step7") ) {
		// List with handle
		var pageTable = document.getElementById("pageTableBody");
		var sortable = Sortable.create(pageTable, {
			sort: true,
			group: 'page-list',
			handle: '.move-handle',
			draggable: '.rows',
			filter: '.hidden, .btn',
			animation: 150,
			// Called by any change to the list (add / update / remove)
			onSort: function (/**Event*/evt) {
				// TODO: open banner that sorting is not saved?
				//var itemEl = evt.item;  // dragged HTMLElement
				// + indexes from onEnd
			},
			// TODO: Totally remove entire store part here, no point since button-pres will store data in another way.
			store: {
				/**
				 * Get the order of elements. Called once during initialization.
				 * @param   {Sortable}  sortable
				 * @returns {Array}
				 */
				get: function (sortable) {
					var order = localStorage.getItem(sortable.options.group);
					// TODO: Fetch data from server? Naah .. not important
					return order ? order.split('|') : [];
				},

				/**
				 * Save the order of elements. Called onEnd (when the item is dropped).
				 * @param {Sortable}  sortable
				 */
				set: function (sortable) {
					var order = sortable.toArray();
					localStorage.setItem(sortable.options.group, order.join('|'));
				}
			}
		});
	};

	// Store the manual sorting of the table with ajax
	// TODO: Store this data thru ajax. Send a list of all the ID's in the order they are now, split it and store it server-side.
	$("body.migrate-step7 .saveOrder").click( function(e) {
		e.preventDefault();

		console.log("*** Let's save! *** (work in progress)");

		// 1. Collect all the ID's, in order
		console.log("** Step 1");
		var order = new Array();
		$("#pageTableBody tr").each( function() {
			order.push($(this).attr("data-pageid"));
		});

		// 2. Build a splittable string of them
		console.log("** Step 2");
		var orderString = order.join('|');
		console.log(orderString);

		// 3. Send ajax call with string, handle response
		console.log("** Step 3");

		console.log(callUrl);
		var callUrl = $(".input_ajaxurl_savesort").val(); // A hidden input field from the calling html page (needed some PHP parsing of the URL)

		$.ajax({
			type: "POST",
			url: callUrl,
			data: {
				"parent": parent_id,
				"child": child_id,
				"undo": undo
			},
			success: function(html) {
				console.log("parent_id: " + parent_id);
				console.log("child_id: " + child_id);

				if ( undo === "false" ) {
					container.addClass("child");
					// Toggle button behaviour now that it is a child
					elem.text("Undo");
					elem.attr("data-makeparent-undo", "true");
				} else {
					container.removeClass("child");
					if (parent_id > 0) {
						elem.text("Add child");
						elem.attr("data-makeparent-undo", "false");
					} else {
						elem.hide();
					}
				}

			},
			error: function(html) {
				container.addClass("error");
				setTimeout( function() { container.removeClass("error"); }, 1000);
				alert(html);
			}
		});

		console.log("*** Done! ***");
	});

});