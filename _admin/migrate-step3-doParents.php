<?php
	// Set up template variables
	$PAGE_step  = 3;
	$PAGE_name  = 'Step ' . $PAGE_step . ' - Guess Parent/childs';
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'guess parent-child relationships';
?>
<?php require('_global.php'); ?>

<?php

	// Form generator
	addField( array(
		"label" => "Regex for finding titles:",
		"id" => "splitcode",
		"type" => "area(10*7)",
		"description" => "Enter any chunk of HTML-code here (use the output bellow as a guide). Use '[*]' as a wildcard, and use '[?]' as the 'locator' (the page will be saved with that name). Only use one 'locator' (but any amount of wildcards).",
		"min" => "2",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );

?>
<?php include('_header.php'); ?>

<?php

	// Don't display all the messages while in split mode
	$split_id = qsGet("split");
	if ( $split_id === "" ) $split_id = 0;

?>

<?php

	// Now that we are just before the form starts, we can output any errors we might have pushed into the error-array.
	// Calling this function outputs every error, earlier pushes to the error-array also stops the saving of the form.

	outputErrors($SYS_errors);

?>

	<div class="row">
		<div class="span12">

<?php

	///////////////////////////////////////////////////
	// Handle posted split form setting/value
	///////////////////////////////////////////////////

	if ( $split_id > 0 ) {

		if (ISPOST)
		{
			validateForm();

			if (empty($SYS_errors)) {

				// Stupid way of getting all the form data into variables for use to save the data.
				$splitcode = $PAGE_form[0]["content"];
				$split_ORG = $splitcode;

				if ( substr_count( $splitcode, "[?]" ) == 1 ) {

					// I owe a lot to http://regex101.com/ for getting this correct! #regex_noob

					// Escape these chars (because they have special meaning in regex)
					$splitcode = str_replace('\\', '\\\\', $splitcode);
					$splitcode = str_replace('.', '\.', $splitcode);
					$splitcode = str_replace('*', '\*', $splitcode);
					$splitcode = str_replace('?', '\?', $splitcode);
					$splitcode = str_replace('+', '\+', $splitcode);
					$splitcode = str_replace('$', '\$', $splitcode);
					$splitcode = str_replace('^', '\^', $splitcode);
					$splitcode = str_replace('{', '\{', $splitcode);
					$splitcode = str_replace('}', '\}', $splitcode);
					$splitcode = str_replace('(', '\(', $splitcode);
					$splitcode = str_replace(')', '\)', $splitcode);

					// Our magic placeholders
					$splitcode = str_replace('[\*]', '.*', $splitcode);
					$splitcode = str_replace('[\?]', '(.*?)', $splitcode);

					$splitcode = str_replace('[', '\[', $splitcode);
					$splitcode = str_replace(']', '\]', $splitcode);

					// Other replacements
					//$splitcode = str_replace('&', '\&', $splitcode);
					$splitcode = str_replace('/', '\/', $splitcode);
					//$splitcode = str_replace('\'', '', $splitcode);

				} else {

					pushError("No [?] added (or more than one), and we need that to find names for the new pages!");

				}

				$PAGE_form[0]["content"] = $split_ORG;

			}

		}

	}

?>

		<?php if ( $split_id > 0 ) { ?>

<form class="well form" action="" method="post">

	<div class="row">
		<div class="span11">

	<?php

		// This is the output area, where all the field's html should be generated for empty field's SQL inserts, and already filled in field's SQL updates.
		// The fields data/content is generated in the upper parts of this document. Just call this function to get the html out.

		outputFormFields();

	?>

			<h3>Settings</h3>

			<label class="checkbox">
				<input type="checkbox" name="keep" value="yes"<?php if (formGet('keep') == "yes") { ?> checked="checked"<?php } ?> />
				Keep the entire matched html-area in the new pages
			</label>

			<hr />

			<h4>Not in use ...</h4>
			<p>Normally the first match on a page is a bit down from the top on that page's text. What do you want to do with all the text before this first match (if any)?</p>
			<label class="radio">
				<input type="radio" name="prematch" value="parent"<?php if (formGet('prematch') == "parent") { ?> checked="checked"<?php } ?> />
				Use it for the Parent-page content
			</label>
			<label class="radio">
				<input type="radio" name="prematch" value="sub"<?php if (formGet('prematch') == "sub") { ?> checked="checked"<?php } ?> />
				Use it as a subpage too
			</label>
			<br />

			<input type="submit" name="titles" value="Find titles" class="btn btn-primary" />

			<a href="<?= $SYS_pageroot ?>migrate-step3.php" class="btn">Cancel</a>

		</div>

	</div>

</form>

		<?php } ?>


<?php

	///////////////////////////////////////////////////
	// Handle splitting of pages (database-part)
	///////////////////////////////////////////////////

	if ($split_id > 0) {

		$result = db_getHtmlFromPage( array(
						'site' => $PAGE_siteid,
						'id' => $split_id
					) );

		if ( isset( $result ) )
		{

			$row = $result->fetch_object();

			// Waterfall-choose the best (cleanest) html from the database depending on which is available
			if ( !is_null($row->clean) ) {

				$codeoutput = $row->clean;

			} elseif ( !is_null($row->tidy) ) {

				$codeoutput = $row->tidy;

			} elseif ( !is_null($row->wash) ) {

				$codeoutput = $row->wash;

			} else {

				$codeoutput = $row->content;

			}

			$baseurl    = $row->page;
			$baseid     = $row->id;

			if (isset($splitcode)) {

				$arr_content = array();
				$arr_titles  = array();

				$arr_content = preg_split( "/" . $splitcode . "/Ui", $codeoutput ); // Find the content
				preg_match_all( "/" . $splitcode . "/Ui", $codeoutput, $arr_titles ); // Find the names//titles

				echo "<h3>We found these sub pages:</h3>";

				// Did we match anything?
				if ( $arr_content !== false && $arr_titles !== false ) {

					$length_arr = count($arr_content);
					$length_title = count($arr_titles[1]);

					$last_title = "";
					$title = "";
					$slug = "";

					for ($i = 0; $i < $length_arr; $i++ ) {

						$show_output = true;

						if ($i <= $length_title && $i+1 < $length_arr) {

							$title   = $arr_titles[1][$i];
							$last_title = $title;

							$content = $arr_content[$i+1];

							// Setting tells us to keep the entire match inside the content of new page
							if ( formGet('keep') == "yes") {
								$content = $arr_titles[0][$i] . $content;
							}

							// Convert page title into something more URL friendly
							$slug = fn_getSlugFromTitle($title);

							$content_db = trim( $content );

							if (formGet("split") == "Run split") {

								$result = db_setNewPage( array(
											'site' => $PAGE_siteid,
											'html' => 'CREATED BY SPLIT-FUNCTION - not from crawl!',
											'page' => $baseurl . '/' . $slug,
											'content' => $content_db,
											'page_slug' => $slug,
											'page_parent' => $baseid,
											'crawled' => 0,
											'title' => $title
										) );

								if ( $result > 0 ) {

									//echo '<div class="alert alert-success"><h4>Save successful</h4><p>New page for ' . $title . ' created, id: ' . $result . '</p></div>';
									fn_infobox("Save successful", 'New page for ' . $title . ' created, id: ' . $result,'');

								}
							} else {

							}

						} else {

							// Code for handling EOF on the page when split matches has been found earlier
							if ( $last_title == $title && $title != "" ) {
								$show_output = false;
								fn_infobox("No more matches!", "That's all the possible matching subpages we could find on the page you submitted. Tweak your 'split-code' if this isn't what you wanted.", ' alert-info');
								// Output a button so the user can return to the main table with all data
								if (formGet("split") == "Run split") {
									echo "<p class=\"text-center\"><a href=\"" . $SYS_pageself . "\" class=\"btn btn-primary btn-large\">Cool, return to page list!</a></p>";
								}
							}

							$title = "NO MATCHING TITLE FOR THIS PAGE!!!"; // This should skip the split
							$content = "no matching content for this page!";

							// Error message for when we found zero hits of that string on this page
							if ( $show_output ) {
								//fn_infobox("Couldn't save", "New page for following code could not be created!<br />If this error appears at the end of a list of new, splitted, pages then it just means we didn't find any more pages.", 'error');
								$show_output = false;
								fn_infobox("Nothing found", "The 'split-code' you submitted didn't exist anywhere on the current page. Try again!", 'error');
							}

						}

						if ( $show_output ) {
							echo "<h4>" . $title . "</h4>";
							echo "<p><span class=\"text-info\">" . $baseurl . '/<strong>' . $slug . "</strong></span></p>";
							echo "<pre>";

							$content = htmlspecialchars($content, ENT_QUOTES, "UTF-8");
							$content = trim( $content );

							echo $content;
							echo "</pre>";
							if (formGet("split") == "Run split") {
								//echo "<p><strong>Result:</strong> <span class=\"label label-success\">Saved</span></p>";
							} else {
								echo "<p><strong>Result:</strong> <span class=\"label label-important\">Not saved</span></p>";
							}
							echo "<hr />";
						}

					}

				} else {
					fn_infobox("Nothing found", "The 'split-code' you submitted didn't exist anywhere on the current page. Try again!", 'error');
				}

			} else {

				$codeoutput = htmlspecialchars($codeoutput, ENT_QUOTES, "UTF-8");

				echo "
					<h4>Source code</h4>
					<pre>" . $codeoutput . "</pre>";

			}

		}

	}

}

?>

		</div>
	</div>


<?php require('_footer.php'); ?>
