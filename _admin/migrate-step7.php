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
		"label" => "HTML-code to split on:",
		"id" => "splitcode",
		"type" => "area(10*7)",
		"description" => "Enter any chunk of HTML-code here (use the output bellow as a guide). Use '[*]'' as a wildcard, and use '[?]' as the 'locator' (the page will be saved with that name). Only use one 'locator' (but any amount of wildcards).",
		"min" => "2",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );

?>
<?php include('_header.php'); ?>

	<div class="alert">
		<h4>Optional step!</h4>
		<p>This step is not mandatory =)</p>
	</div>

	<div class="row">
		<div class="span8">
			<h2>Manage pages</h2>
			<p>
				Here you can manage your pages that you crawled in Step 1. The reason you didn't get to this step right after is because the pages are easier to work with here if reduntant html-code
				have been stripped away.
			</p>
			<p>
				It's possible to access this step later and delete and duplicate pages. However, the split-function only runs on the data that comes from Step 2.
			</p>

			<div class="alert alert-block alert-success">
				<h4>No Save-button!?</h4>
				<p>
					When you're ready with all pages you wanna export, manually <a href="<?= $SYS_pageroot ?>migrate-step8.php">go to Step 8</a>.
					All the pages under here, and their cleaned and washed html-code, will be exported to XML in the next step.
				</p>
			</div>

		</div>

		<div class="span3 offset1">
			<h4>Info</h4>
			<p>
				Info ...
			</p>
		</div>

	</div>

<?php

	// Now that we are just before the form starts, we can output any errors we might have pushed into the error-array.
	// Calling this function outputs every error, earlier pushes to the error-array also stops the saving of the form.

	outputErrors($SYS_errors);

?>

	<div class="row">
		<div class="span12">

			<a class="btn btn-success" href="<?= $SYS_pageself ?>"><i class="icon-plus-sign icon-white"></i> Add new Page</a>

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

	$split_id = qsGet("split");

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
			if ( $row->id == $split_id ) {
				echo '<tr class="selected">';
			} else {
				echo '<tr>';
			}

			if ($split_id > 0) {
				echo "<td>-</td>";
			} else {
				echo "<td><a href=\"" . $SYS_pageself . "?split=" . $row->id . "\" class=\"btn btn-mini btn-primary\">Edit HTML</a></td>";
			}

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

<?php

	}

?>

		<?php if ( $split_id > 0 ) { ?>

<!-- <form class="form-horizontal" action="" method="post"> -->
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

			<input type="submit" name="split" value="Run split" class="btn btn-primary" />

			<input type="submit" name="split" value="Test split" class="btn" />

			<a href="<?= $SYS_pageself ?>" class="btn">Cancel split</a>

		</div>

	</div>

</form>

		<?php } ?>


<?php

// The actual code
// ****************************************************************************

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

/*
						// What to get is controlled by a setting (getting only the exact match is default).
						if ( formGet('keep') == "yes") {
							$title   = $arr_titles[0][$i];
						} else {
							$title   = $arr_titles[1][$i];
						}
*/

// This function is totally wrong ... it should go from first match and to beginning of file, not increment the array counter
if ( 1 === 3 ) {
						if ( formGet('prematch') == "sub") {
							$content = $arr_content[$i]; // Do not skip first content (setting tells us to create a subpage out of it)
						} else {
							$content = $arr_content[$i+1]; // Skip first content (because of same setting)

							// Setting to save everything before first match as parent content
							if ($i === 1 && formGet('prematch') == "parent") {
								$parentcontent = $arr_content[$i];
								// TODO: Add this to parent content ...
							}
						}
}

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
/*
				if ( mb_detect_encoding($codeoutput, "utf-8, iso-8859-1") == "UTF-8" ) {
					$codeoutput;
				} else {
					$codeoutput = iconv("iso-8859-1", "utf-8", $codeoutput);
				}
*/
				//$codeoutput = htmlentities( $codeoutput );
				$codeoutput = htmlspecialchars($codeoutput, ENT_QUOTES, "UTF-8");

				echo "<pre>" . $codeoutput . "</pre>";

			}

		}

	}

?>

		</div>
	</div>


<?php require('_footer.php'); ?>
