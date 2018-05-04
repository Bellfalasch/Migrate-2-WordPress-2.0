<?php
	// Set up template variables
	$PAGE_step  = 3;
	$PAGE_name  = 'Step ' . $PAGE_step;
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'manage your pages: split, duplicate, or delete';
?>
<?php require('_global.php'); ?>

<?php

	// Form generator
	addField( array(
		"label" => "HTML-code to split on:",
		"id" => "splitcode",
		"type" => "area(10*7)",
		"description" => "Enter any chunk of HTML-code here (use the output below as a guide). Use '[*]' as a wildcard, and use '[?]' as the 'locator' (the page will be saved with that name). Only use one 'locator' (but any amount of wildcards).",
		"min" => "2",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );

?>
<?php include('_header.php'); ?>

<?php
	// Parent: add children-mode
	$makeparent_id = qsGet("parent");
	if ( $makeparent_id === "" ) $makeparent_id = 0;

	// Don't display all the messages while in split mode
	$split_id = qsGet("split");
	if ( $split_id === "" ) $split_id = 0;

	if ( $split_id === 0 ) {
?>
	<div class="alert">
		<h4>Optional step!</h4>
		<p>This step is not mandatory =)</p>
	</div>

	<div class="row">
		<div class="span12">
			<h2>Manage pages</h2>
			<p>
				Here you can manage your pages that you crawled in Step 1. The reason you didn't get to this step right after is because the pages are easier to work with here if reduntant html-code
				have been stripped away.
			</p>
			<p>
				It's possible to access this step later and delete and duplicate pages. However, the split-function only runs on the data that comes from Step 2.
			</p>
		</div>

		<div class="span3">
			<h4>Split!</h4>
			<p>
				This function is extremely powerful when changing your site structure. You select one page to the left, and after that
				get to write a small "needle"-code that we will look for in the code. For each match we will create a new sub-page of the
				selected page. Brilliant for splitting long long pages into sub-pages instead.
			</p>
		</div>
		<div class="span3">
			<h4>Duplicate</h4>
			<p>
				This will make a copy of that page (with new unique slug/url). You'd normally use this function when you have an old page you want to cut up in some unique ways that "Split" can't help you with.
			</p>
		</div>
		<div class="span3">
			<h4>Guess titles</h4>
			<p>
				Titles are tricky, the Crawl-function takes it from the filename. That doesn't always work, so this function let's you run a custom Regex finding patterns in your HTML to use for Titles instead! If the suggestions doesn't suit you, you can just cancel and do it manually.
			</p>
			<p>
				<a class="btn btn-success" href="<?= $SYS_pageroot ?>migrate-step3-doTitles.php"><i class="icon-th-list icon-white"></i> Guess!</a>
			</p>
		</div>
		<div class="span3">
			<h4>Guess parent/child</h4>
			<p>
				We will try to analyze the URLs to figure out a two-dimensional herarki of items. You'll be suggested a new site structure with parent+child combinations of your pages. If you whish, you can store that with the click of just one button. You still can adjust this manually afterwards.
			</p>
			<p>
				<a class="btn btn-success" href="<?= $SYS_pageroot ?>migrate-step3-doParents.php"><i class="icon-indent-left icon-white"></i> Guess!</a>
			</p>
		</div>

	</div>
<?php } ?>

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


	///////////////////////////////////////////////////
	// Duplicate page
	///////////////////////////////////////////////////

	$dup_id = qsGet("dup");

	if ( $dup_id > 0 ) {

		$dup = db_setDuplicatePage( array(
					'id' => $dup_id,
					'site' => $PAGE_siteid
				) );

		if ($dup >= 0) {
			fn_infobox("Duplication successful", "The selected page has been duplicated.",'');
		} else {
			pushError("Duplication of page failed, please try again.");
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

			<input type="submit" name="split" value="Run split" class="btn btn-primary" />

			<input type="submit" name="split" value="Test split" class="btn" />

			<a href="<?= $SYS_pageself ?>" class="muted" style="margin-left:20px;">Cancel split</a>

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
				echo "using row->content";

			}

			$baseurl    = $row->page;
			$baseid     = $row->id;

			if (isset($splitcode)) {

				//$clean = preg_replace( "/<!--(.*)-->/Uis", "$0", $codeoutput );

				$arr_content = array();
				$arr_titles  = array();

				$arr_content = preg_split( "/" . $splitcode . "/u", $codeoutput ); // Find the content
				preg_match_all( "/" . $splitcode . "/u", $codeoutput, $arr_titles ); // Find the names//titles
/*
				echo '<h2>$splitcode:</h2>';
				var_dump( $splitcode );
				echo '<h2>$arr_content:</h2>';
				var_dump( $arr_content );
				echo '<h2>$arr_titles:</h2>';
				var_dump( $arr_titles );
*/
				// Pseudo:
				// arr_titles(1) innehåller match-array, början på 0.
				// arr_content(0) är all kod innan första match, arr_content(1) och upp alla matcher

				//exit;

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

							// arr_titles first array dimension: 0 contains entire matching area, index 1 only the extracted match.

							$title   = $arr_titles[1][$i];
							$last_title = $title;

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

	/*
	Code to test with. First the regex, then that same code translated into our more simpler format

	<a name="\.*"><\/a><\/p>
	<table cellpadding="1">
	<tr>
	<th>(.*?)<\/span> <span class="td_svag">\(.* HP\)<\/th>
	<\/tr>
	<\/table>

	<a name="[*]"></a></p>
	<table cellpadding="1">
	<tr>
	<th>[?]</span> <span class="td_svag">([*] HP)</th>
	</tr>
	</table>
	*/

							} else {

								// Test split (print debugging info)
/*
								var_dump( array(
											'site' => $PAGE_siteid,
											'html' => 'CREATED BY SPLIT-FUNCTION - not from crawl!',
											'page' => $baseurl . '/' . $slug,
											'content' => $content_db,
											'page_slug' => $slug,
											'page_parent' => $baseid,
											'crawled' => 0,
											'title' => $title
										) );
*/
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

			}

			// Show the page's original HTML (handy for "debugging")
			if (!$show_output) {
				$codeoutput = htmlspecialchars($codeoutput, ENT_QUOTES, "UTF-8");
				echo "
					<h4>Source code</h4>
					<pre>" . $codeoutput . "</pre>";
			}

		}

	}


if ( $split_id === 0 ) {

	$result = db_getPagesFromSite( array('site'=>$PAGE_siteid) );

	if ( isset( $result ) )
	{

?>
		<h2>Updating pages</h2>
		<p>
			To update the title (and/or slug) of a page, you can just edit the Title-field and
			the slug field will update automatically.<br />
			<strong>Save:</strong> Leave the field to save your changes instantly (you can use the tab button too).<br />
			<strong>Undo:</strong> Press Ctrl + Z (or Cmd + Z on Mac) to regret any of your changes before leaving the input field.<br />
			<strong>Visual Feedback:</strong> The input field will light up green for a short while when changes have been stored, or red if an error occured.
		</p>
		<input type="hidden" name="ajaxurl_title" class="input_ajaxurl_title" value="<?= $SYS_pageroot ?>migrate-step3-savetitle.php" />
		<input type="hidden" name="ajaxurl_del" class="input_ajaxurl_del" value="<?= $SYS_pageroot ?>migrate-step3-delete.php" />
		<input type="hidden" name="ajaxurl_child" class="input_ajaxurl_child" value="<?= $SYS_pageroot ?>migrate-step3-setparent.php" />

		<table id="pageTable">
			<thead>
				<th>-</th>
				<th>-</th>
				<th>Title</th>
				<th>Slug</th>
				<th>URL</th>
				<th>-</th>
				<th>-</th>
			</thead>
			<tbody>

<?php

		$i = 1; // Used for tabindexing on the input fields, so not a normal row incrementor
		while ( $row = $result->fetch_object() )
		{
			$addclass = "";

			// Add child-class to children so you see it visually
			if ( $row->page_parent > 0 ) {
				$addclass = "child";
			}
			if ($row->id == $split_id || $row->id == $makeparent_id ) {
				$addclass = " selected";
			}

			// Deleted page?
			if ( $row->deleted ) {
				$addclass .= " hidden";
			}

			echo "<tr";
			if ( trim($addclass) != "" ) {
				echo " class=\"" . trim( $addclass ) . "\"";
			}
			echo ">";

			// Split-column
			if ($split_id > 0 || $makeparent_id > 0) {
				echo "<td>-</td>";
			} else {
				echo "<td><a href=\"" . $SYS_pageself . "?split=" . $row->id . "\" class=\"btn btn-mini btn-primary\">Split</a></td>";
			}

			// "Select as parent, then select it's children"-column
			if ($makeparent_id > 0 || $split_id > 0) {
				if ($row->id == $makeparent_id ) {
					echo "<td><a href=\"" . $SYS_pageself . "#pageTable\" class=\"btn btn-mini btn-primary\">Done</a></td>";
				} else {
					echo "<td>";
					if ($row->page_parent == 0 ) {
						echo "<button data-makeparent-undo=\"false\" data-makeparent-parent=\"" . $makeparent_id . "\" data-makeparent-child=\"" . $row->id . "\" class=\"btn btn-mini addChild\">Add child</button>";
					} else {
						echo "-";
					}
					echo "</td>";
				}
			} else {
				if ($row->page_parent == 0 ) {
					echo "<td><a href=\"" . $SYS_pageself . "?parent=" . $row->id . "#pageTable\" class=\"btn btn-mini btn-primary\">Parent</a></td>";
				} else {
					echo "<td><button data-makeparent-undo=\"true\" data-makeparent-parent=\"0\" data-makeparent-child=\"" . $row->id . "\" class=\"btn btn-mini addChild\">Un-child</button></td>";
				}
			}

			$page = $row->page;
			$url = str_replace( $PAGE_siteurl, "/", $page );
			$title = $row->title;

			// Title is not set in the database
			if ( is_null($title) ) {
				$title = "<em>- Unknown -</em>";
			}

			//echo "<td>" . $title . "</td>";
			echo "<td class=\"control-group\"><input type=\"text\" name=\"" . $row->id . "_title\" data-original-value=\"" . $title . "\" value=\"" . $title . "\" tabindex=\"" . $i++ . "\" /></td>";
			//echo "<td>" . $row->page_slug . "</td>";
			echo "<td class=\"control-group\"><input type=\"text\" name=\"" . $row->id . "_slug\" data-original-value=\"" . $row->page_slug . "\" value=\"" . $row->page_slug . "\" tabindex=\"" . $i++ . "\" /></td>";
			echo "<td>";

			if ( $row->crawled == "1" ) {
				echo "<a href=\"" . $page . "\" target=\"_blank\" title=\"Click to open the original crawled page\">";
				echo $url;
				echo "</a>";
			} else {
				echo $url;
			}

			echo "</td>";
			echo "<td><a href=\"" . $SYS_pageself . "?dup=" . $row->id . "\" class=\"btn btn-mini btn-warning\">Duplicate</a></td>";
			echo "<td><button data-delete=\"true\" data-delete-id=\"" . $row->id . "\" class=\"btn btn-mini btn-danger delete\">Delete</button></td>";
			echo '</tr>';
		}

?>
			</tbody>
		</table>

<?php

	}
}

?>

		</div>
	</div>


<?php require('_footer.php'); ?>
