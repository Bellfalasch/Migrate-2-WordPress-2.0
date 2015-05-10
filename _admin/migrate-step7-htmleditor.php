<?php
	/* Set up template variables */
	$PAGE_title = "HTML editor";
?>
<?php require('_global.php'); ?>
<?php

	// Form generator
	addField( array(
		"label" => "HTML source:",
		"id" => "html",
		"type" => "area(6*12)",
//		"description" => "Change the washed and cleaned HTML. Don't forget to save!",
		"min" => "1",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );


	////////////////////////////////////////////////////////
	// HANDLE POST AND SAVE CHANGES

	if (ISPOST)
	{
		// This line is needed to call the validation-process of your form!
		validateForm();

		// Stupid way of getting all the form data into variables for use to save the data.
		$formHTML = $PAGE_form[0]["content"];
		$formID   = formGet("QSid");
		$PAGE_dbid = $formID;

		// If no errors:
		if (empty($SYS_errors)) {

			// Call function in "_database.php" that does the db-handling, send in an array with data
			$result = db_setCleanCode( array(
						'clean' => $formHTML,
						'id' => $formID
					) );

			// This is the result from the db-handling in my files.
			// (On update they return -1 on error, and 0 on "no new text added, but the SQL worked", and > 0 for the updated posts id.)
			if ($result >= 0) {
				fn_infobox("Save successful", "Data updated",'');
				//header('Location: ' . $SYS_pageself . '?saved=true');
			} else {
				pushError("Data could not be saved, do retry.");
			}

		}

	}

	if ( $PAGE_dbid > 0 ) {

		////////////////////////////////////////////////////////
		// If first load, fetch HTML from database

//		if ( !ISPOST )
//		{

			$result = db_getHtmlFromPage( array(
							'site' => $PAGE_siteid,
							'id' => $PAGE_dbid
						) );

			// If anything was found, put it into pur PAGE_form
			if (!is_null($result))
			{
				$row = $result->fetch_object();

				// Waterfall-choose the best (cleanest) html from the database depending on which is available
				if ( !is_null($row->clean) ) {

					$html = $row->clean;

				} elseif ( !is_null($row->tidy) ) {

					$html = $row->tidy;

				} elseif ( !is_null($row->wash) ) {

					$html = $row->wash;

				} elseif ( !is_null($row->content) ) {

					$html = $row->content;

				} else {

					$html = "<!-- Empty page -->";

				}

				// Stupid way of doing it ... no function yet to bind database table to the form, sorry =P
				$PAGE_form[0]["content"] = $html;

				//$title = $row->title;

			} else {
				pushError("Couldn't find the requested page's HTML!");
			}

//		}

	} else {
		pushError("No Page selected =/");
	}

?>

<?php

	// Now that we are just before the form starts, we can output any errors we might have pushed into the error-array.
	// Calling this function outputs every error, earlier pushes to the error-array also stops the saving of the form.

	outputErrors($SYS_errors);

?>

	<?php if ( $PAGE_dbid > 0 ) { ?>

<form class="well form" action="<?= $SYS_pageself ?>?id=<?= $PAGE_dbid ?>" method="post" id="ajax-form">

	<?php

		// This is the output area, where all the field's html should be generated for empty field's SQL inserts, and already filled in field's SQL updates.
		// The fields data/content is generated in the upper parts of this document. Just call this function to get the html out.

		outputFormFields();

	?>
	<input type="hidden" name="QSid" value="<?= $PAGE_dbid ?>" />

	<button type="submit" class="btn btn-primary pull-left">Save changes</button>
	<p class="hidden pull-left">Message</p>

</form>

	<?php } ?>
