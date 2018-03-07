<?php
	// Set up template variables
	$PAGE_step  = 3;
	$PAGE_name  = 'Step ' . $PAGE_step;
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'guess titles based on crawled html-code';
?>
<?php require('_global.php'); ?>

<?php

	// Form generator
	addField( array(
		"label" => "Regex for finding titles:",
		"id" => "titleregex",
		"type" => "area(10*7)",
		"description" => "Write your Regex pattern here. Use it to locate and capture what is to be used as page titles. Only use one capturing group. Example: '&lt;h1&gt;(.*)&lt;/h1&gt;' will find all h2-tags and use the contents of those as titles.",
		"min" => "2",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );

?>
<?php include('_header.php'); ?>

<link rel="stylesheet" href="<?= $SYS_pageroot ?>assets/highlight/default.css">
<link rel="stylesheet" href="<?= $SYS_pageroot ?>assets/lightcase/css/lightcase.css">
<script src="<?= $SYS_pageroot ?>assets/highlight/highlight.pack.js"></script>
<script src="<?= $SYS_pageroot ?>assets/lightcase/js/lightcase.js"></script>
<script>
  hljs.initHighlightingOnLoad();

	jQuery(document).ready(function($) {
		// On the fly
		$('.btn.lightcaseHtml').click(function (event) {
			event.preventDefault();

			lightcase.start({
				href: '#',
				maxWidth: 640,
				maxHeight: 400,
				onFinish: {
					injectContent: function () {
						var content = '<div style="text-align: center;"><h4>On the fly!</h4><p>Yes, right! This popup was called without any DOM object and initialization before by using the tag attributes or so. A common use case for using this could be to automatically invoke a popup after few time, or if lightcase not plays the lead but for instance just needs to show a note, accepting or refusing policy etc.<br><br>Important for this is to set <b>href: \'#\'</b> in your options to open a blank box which you can fill with content afterwards by using the <b>onFinish hook</b>.</p></div>';

						// Find the innermost element and feed with content.
						// Can be different according to the media type!
						lightcase.get('contentInner').children().html(content);
						// Do a resize now after filling in the content
						lightcase.resize();
					}
				}
			});
		});
	});
</script>


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

	if (ISPOST)
	{
		validateForm();

		// If we get no errors, extract the form values.
		if (empty($SYS_errors)) {
			$splitcode = $PAGE_form[0]["content"];
		}

	}

?>

<h2>Title guesser</h2>
<form class="well form" action="" method="post">

	<div class="row">
		<div class="span11">

	<?php

		// This is the output area, where all the field's html should be generated for empty field's SQL inserts, and already filled in field's SQL updates.
		// The fields data/content is generated in the upper parts of this document. Just call this function to get the html out.

		outputFormFields();

	?>

			<h3>Settings</h3>

			<p>What data to find the titles in?</p>
			<label class="radio">
				<input type="radio" name="target" value="full"<?php if (formGet('target') == "full" || formGet('target') === '') { ?> checked="checked"<?php } ?> />
				Full crawled HTML
			</label>
			<label class="radio">
				<input type="radio" name="target" value="stripped"<?php if (formGet('target') == "stripped") { ?> checked="checked"<?php } ?> />
				Stripped HTML
			</label>
			<br />

			<p>
				After pressing "Find titles" we'll show you the result but nothing is actually saved until
				after you verify it. So keep tuning that Regex until it's perfect!
			</p>

			<input type="submit" name="titles" value="Find titles" class="btn btn-primary" />

			<a href="<?= $SYS_pageroot ?>migrate-step3.php" class="btn">Cancel</a>

		</div>

	</div>

</form>


<?php

	///////////////////////////////////////////////////
	// Handle splitting of pages (database-part)
	///////////////////////////////////////////////////

	if (ISPOST) {

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


	$result = db_getPagesFromSite( array('site'=>$PAGE_siteid) );

	if ( isset( $result ) )
	{
?>

<h3>Current structure</h3>
<table id="pageTable">
	<thead>
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

	// Deleted page?
	if ( $row->deleted ) {
		$addclass .= " hidden";
	}

	echo "<tr";
	if ( trim($addclass) != "" ) {
		echo " class=\"" . trim( $addclass ) . "\"";
	}
	echo ">";

	$page = $row->page;
	$url = str_replace( $PAGE_siteurl, "/", $page );

	$title = $row->title;
	if ( is_null($title) ) {
		$title = "<em>- Unknown -</em>";
	}

	echo "<td>" . $title . "</td>";
	echo "<td>" . $row->page_slug . "</td>";

	echo "<td>";
	if ( $row->crawled == "1" ) {
		echo "<a href=\"" . $page . "\" target=\"_blank\" title=\"Click to open the original crawled page\">";
		echo $url;
		echo "</a>";
	} else {
		echo $url;
	}
	echo "</td>";
	echo "<td><a href=\"#\" class=\"lightcaseHtml btn btn-mini\" data-id=\"" . $row->id . "\" data-rel=\"lightcase\" title=\"Full HTML for '" . $title . "'\">See full HTML</a></td>";
	echo "<td><a href=\"#\" class=\"btn btn-mini\">See stripped</a></td>";

	echo '</tr>';
}
//<pre><code class="html">...</code></pre>
?>
	</tbody>
</table>

<?php } ?>
	</div>
</div>


<?php require('_footer.php'); ?>
