<?php
	/* Set up template variables */
	$PAGE_step  = 7;
	$PAGE_name  = 'Step ' . $PAGE_step;
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'administrate your pages';
?>
<?php require('_global.php'); ?>
<?php

	// Form generator
	addField( array(
		"label" => "Page Title:",
		"id" => "title",
		"type" => "text(3)",
		"description" => "Set the Title to be used for this page",
		"min" => "2",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );

?>
<?php include('_header.php'); ?>


<?php

	////////////////////////////////////////////////////////
	// HANDLE POST AND SAVE CHANGES

	if (ISPOST)
	{
		// This line is needed to call the validation-process of your form!
		validateForm();

//			var_dump($PAGE_form); // For debugging

		// Stupid way of getting all the form data into variables for use to save the data.
		$formTitle = $PAGE_form[0]["content"];

		// If no errors:
		if (empty($SYS_errors)) {

			// Call insert-function from our database-file for admin.
			$result = db_setPageSimple( array(
						'title' => $formTitle,
						'site' => $PAGE_siteid
					) );

			// If the insert worked we will now have the created id in this variable, otherwhise we will have 0 or -1.
			if ($result > 0) {

				header('Location: ' . $SYS_pageself . '?saved=' . $result);

			} else {
				pushError("Data could not be saved, do retry.");
			}
		}

	}

?>
	<div class="alert">
		<h4>Optional step!</h4>
		<p>This step is not mandatory =)</p>
	</div>

	<div class="row">
		<div class="span7">
			<h2>Finalize pages</h2>
			<p>
				This is the end, the last step before we'll hand you that final XML export that you can import into WordPress.
				Now's the chance to fine tune your pages HTML and a quick and easy way. You can also create new pages, if that's something you'd want.
			</p>

			<div class="alert alert-block alert-success">
				<h4>No Save-button!?</h4>
				<p>
					When you're ready with all pages you wanna export, manually <a href="<?= $SYS_pageroot ?>migrate-step8.php">go to Step 8</a>.
					All the pages under here, and their cleaned and washed html-code, will be exported to XML in the next step.
				</p>
			</div>

		</div>

		<div class="span4 offset1">

<form class="well form" action="" method="post">

			<h4>Create new Pages</h4>
			<p>
				Sometimes you just need a few brand new pages to import into WordPress. Just create them here.
			</p>

			<?php

				// This is the output area, where all the field's html should be generated for empty field's SQL inserts, and already filled in field's SQL updates.
				// The fields data/content is generated in the upper parts of this document. Just call this function to get the html out.

				outputFormFields();

			?>

			
			<button type="submit" class="btn btn-primary">Create Page</button>
			

</form>

		</div>

	</div>

<?php

	// Now that we are just before the form starts, we can output any errors we might have pushed into the error-array.
	// Calling this function outputs every error, earlier pushes to the error-array also stops the saving of the form.

	outputErrors($SYS_errors);

?>

	<div class="row">
		<div class="span12">

<!--			<a class="btn btn-success" href="<?= $SYS_pageself ?>"><i class="icon-plus-sign icon-white"></i> Add new Page</a>-->

<?php

// Delete page
// ****************************************************************************

	$del_id = qsGet("del");

	if ( $del_id > 0 ) {

		$del = db_delPage( array(
					'id' => $del_id,
					'site' => $PAGE_siteid
				) );

		if ($del >= 0) {
			//echo "<div class='alert alert-success'><h4>Delete successful</h4><p>The selected page has been deleted.</p></div>";
			fn_infobox("Delete successful", "The selected page has been deleted.",'');
		} else {
			pushError("Delete of page failed, please try again.");
		}

	}


// List pages
// ****************************************************************************

	$result = db_getPagesFromSite( array('site'=>$PAGE_siteid) );

	if ( isset( $result ) )
	{

?>
		<table class="site-list">
			<thead>
				<th>-</th>
				<th>Title</th>
				<th>URL</th>
				<th>-</th>
			</thead>
			<tbody>

<?php

		while ( $row = $result->fetch_object() )
		{
			echo '<tr>';

			$page = $row->page;
			$url = str_replace( $PAGE_siteurl, "/", $page );
			$title = $row->title;

			// Title is not set in the database, so "calculate" one from the page URL
			if ( is_null($title) ) {
				$title = $url;
				$title = str_replace( "/", "", $title );
				$title = str_replace( array('aspx','asp','php','html','htm'), array('','','','',''), $title );
				$title = str_replace( ".", "", $title );
				$title = str_replace( "-", " ", $title );

				// If the URL contains a ? then we should remove it and everything before it
				if ( strpos($title, "?") > 0) {
					$title = strstr( $title, "?" );
					$title = str_replace( "?", "", $title );
				}

				$title = ucwords($title);
			}

			echo "<td><a href=\"" . $SYS_pageroot . "migrate-step7-htmleditor.php?id=" . $row->id . "\" class=\"btn btn-mini btn-primary\" data-title=\"" . $title . "\" data-toggle=\"modal\" data-target=\"#html-modal\">Edit HTML</a></td>";

			echo "<td>" . $title . "</td>";
			echo "<td>";

			if ( $row->crawled == 1 ) {
				echo "<a href=\"" . $page . "\" target=\"_blank\" title=\"Click to open the original crawled page\">";
				echo $url;
				echo "</a>";
			} else {
				echo $url;
			}

			echo "</td>";
			echo "<td><a href=\"" . $SYS_pageself . "?del=" . $row->id . "\" class=\"btn btn-mini btn-danger\">Delete</a></td>";
			echo '</tr>';
		}

?>
			</tbody>
		</table>

		<!-- Modal -->
		<div id="html-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">Edit page HTML</h3>
			</div>
			<div class="modal-body">
				<p>One fine body ...</p>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				<button class="btn btn-primary">Save changes</button>
			</div>
		</div>

<?php

	}

?>

<?php

// Create new page
// ****************************************************************************

	if (1 > 0) {

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

				//$clean = preg_replace( "/<!--(.*)-->/Uis", "$0", $codeoutput );

				$arr_content = array();
				$arr_titles  = array();

				$arr_content = preg_split( "/" . $splitcode . "/Ui", $codeoutput ); // Find the content
				preg_match_all( "/" . $splitcode . "/Ui", $codeoutput, $arr_titles ); // Find the names

				//var_dump( $arr_content );
				//var_dump( $arr_titles );

				// Pseudo:
				// arr_titles(1) innehåller match-array, början på 0.
				// arr_content(0) är all kod innan första match, arr_content(1) och upp alla matcher

				//exit;

				//$codeoutput = htmlentities( $codeoutput );

				echo "<strong>We found these sub pages:</strong>";
				echo "<pre>";

				$length_arr = count($arr_content);
				$length_title = count($arr_titles[1]);

				for ($i = 0; $i < $length_arr; $i++ ) {

					if ($i <= $length_title && $i+1 < $length_arr) {

						// arr_titles first array dimension: 0 contains entire matching area, index 1 only the extracted match.

						$title   = $arr_titles[1][$i];

						$content = $arr_content[$i+1];

						// Setting tells us to keep the entire match inside the content of new page
						if ( formGet('keep') == "yes") {
							$content = $arr_titles[0][$i] . $content;
						}

						// Convert page title into something more URL friendly
						$title_db = trim( strtolower($title) );
						$title_db = str_replace(' ', '-', $title_db); // Space to dash
						$title_db = str_replace(',', '', $title_db); // Everything else removed
						$title_db = str_replace('.', '', $title_db);
						$title_db = str_replace('&', '', $title_db);
						$title_db = str_replace('%', '', $title_db);
						$title_db = str_replace('#', '', $title_db);
						$title_db = str_replace('\'', '', $title_db);
						$title_db = str_replace('"', '', $title_db);
						$title_db = urlencode( $title_db );

						$content_db = trim( $content );

						if (formGet("split") == "Run split") {

							$result = db_setNewPage( array(
										'site' => $PAGE_siteid,
										'html' => 'CREATED FROM STEP 3 - not from crawl!',
										'clean' => null,
										'content' => $content_db,
										'page' => $baseurl . '?' . $title_db
									) );

							if ( $result > 0 ) {

								//echo '<div class="alert alert-success"><h4>Save successful</h4><p>New page for ' . $title . ' created, id: ' . $result . '</p></div>';
								fn_infobox("Save successful", 'New page for ' . $title . ' created, id: ' . $result,'');

							}

						} else {

							var_dump( array(
									'site' => $PAGE_siteid,
									'html' => 'CREATED FROM STEP 3 - not from crawl!',
									'clean' => null,
									'content' => $content_db,
									'page' => $baseurl . '?' . $title_db
								) );

						}

					} else {

						$title = "NO MATCHING TITLE FOR THIS PAGE!!!"; // This should skip the split
						$content = "no matching content for this page!";

						//echo '<div class="alert alert-error"><h4>Couldn\'t save</h4><p>New page for following code could not be created!</p></div>';
						fn_infobox("Couldn't save", "New page for following code could not be created!", 'error');

					}

					echo "<p>";
					echo "<strong>" . $title . "</strong><br />";

					$content = htmlspecialchars($content, ENT_QUOTES, "UTF-8");

					echo $content;
					echo "</p>";

				}

				echo "</pre>";

			} else {

				$codeoutput = htmlspecialchars($codeoutput, ENT_QUOTES, "UTF-8");

				echo "<pre>" . $codeoutput . "</pre>";

			}

		}

	}

?>

		</div>
	</div>


<?php require('_footer.php'); ?>
