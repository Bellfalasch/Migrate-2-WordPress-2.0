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

<?php

	// Now that we are just before the form starts, we can output any errors we might have pushed into the error-array.
	// Calling this function outputs every error, earlier pushes to the error-array also stops the saving of the form.

	outputErrors($SYS_errors);

?>

	<div class="row">
		<div class="span12">

<?php

	///////////////////////////////////////////////////
	// Handle posted regex
	///////////////////////////////////////////////////

	if (ISPOST)
	{
		validateForm();

		// If we get no errors, extract the form values.
		if (empty($SYS_errors)) {
			$regex = $PAGE_form[0]["content"];
			//$regex = str_replace('/', '\/', $regex);
			$target = formGet('target') === "stripped" ? 'content' : 'html';
		}

	}

?>

<h2>Title guesser</h2>
<?php if (qsGet("do") !== "save") { ?>
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
<?php } ?>


<?php

	///////////////////////////////////////////////////
	// Handle saving of the suggested names.
	///////////////////////////////////////////////////

	if (ISPOST && qsGet("do") === "save") {
		foreach($_POST['page-id'] as $key => $val) {
			$id = $val;
			$title = null;
			$slug = null;
			$updTitle = false;
			$updSlug = false;
			$return = 0;
			if (!empty($_POST['page-title'][$key])) {
				$title = $_POST['page-title'][$key];
				$updTitle = true;
			}
			if (!empty($_POST['page-slug'][$key])) {
				$slug = $_POST['page-slug'][$key];
				$updSlug = true;
			}
			if ($updTitle && $updSlug) {
				$return = db_setTitleAndSlug( array(
					'title' => $title,
					'slug' => $slug,
					'id' => $id,
					'site' => $PAGE_siteid
				));
			} elseif ($updTitle && !$updSlug) {
				$return = db_setOnlyTitle( array(
					'title' => $title,
					'id' => $id,
					'site' => $PAGE_siteid
				));
			} elseif (!$updTitle && $updSlug) {
				$return = db_setOnlySlug( array(
					'slug' => $slug,
					'id' => $id,
					'site' => $PAGE_siteid
				));
			}
			if ($return > 0) {
				echo "<strong>Saved:</strong> " . $id . " - " . $title . " - " . $slug . "<br />";
			}
		}
	}

if (qsGet("do") !== "save") {

	$result = db_getPagesFromSite( array('site'=>$PAGE_siteid) );

	if ( isset( $result ) )
	{
?>

<h3><?php if (ISPOST) { ?>Suggested<?php } else { ?>Current<?php } ?> structure</h3>
<form method="post" action="?do=save">
	<table id="pageTable" data-ajax-html="<?= $SYS_pageroot ?>ajax/getHtml.php">
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
	// Deleted page?
	if ( $row->deleted ) {
		continue;
	}

	$addclass = "";
	// Add child-class to children so you see it visually
	if ( $row->page_parent > 0 ) {
		$addclass = "child";
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

	if (ISPOST) {
		$thisPage = db_getHtmlFromPage( array(
				'site' => $PAGE_siteid,
				'id' => $row->id
		));
		if (!is_null($thisPage))
		{
			$rows = $thisPage->fetch_object();

			if ( $target === "html" ) {
				$html = $rows->html;
			} else {
				$html = $rows->content;
			}
		}

		preg_match( "/" . $regex . "/Ui", $html, $matches ); // Find the names//titles

		$newTitle = $matches[1];
		$newSlug = fn_getSlugFromTitle($newTitle);
	}

	echo "<td>";
	if (ISPOST) { echo '<input type="hidden" name="page-id[]" value="' . $row->id . '" />'; }
	if (ISPOST && $newTitle != $title) { echo '<input type="text" name="page-title[]" value="' . $newTitle . '" /> <span class="muted" style="text-decoration:line-through;">'; }
	echo $title;
	if (ISPOST) { echo '</span>'; }
	echo "</td>";
	echo "<td>";
	if (ISPOST && $newSlug != $row->page_slug) { echo '<input type="text" name="page-slug[]" value="' . $newSlug . '" /> <span class="muted" style="text-decoration:line-through;">'; }
	echo $row->page_slug;
	if (ISPOST) { echo '</span>'; }
	echo "</td>";

	echo "<td>";
	if ( $row->crawled == "1" ) {
		echo "<a href=\"" . $page . "\" target=\"_blank\" title=\"Click to open the original crawled page\">";
		echo $url;
		echo "</a>";
	} else {
		echo $url;
	}
	echo "</td>";
	echo "<td><a href=\"#\" class=\"lightcaseHtml btn btn-mini\" data-type=\"full\" data-id=\"" . $row->id . "\" data-rel=\"lightcase\" title=\"Full HTML for '" . $title . "'\">See full HTML</a></td>";
	echo "<td><a href=\"#\" class=\"btn btn-mini\" data-type=\"stripped\" data-id=\"" . $row->id . "\" data-rel=\"lightcase\" title=\"Stripped down HTML for '" . $title . "'\">See stripped</a></td>";

	echo '</tr>';
}
?>
		</tbody>
	</table>
<?php if (ISPOST) { ?>
	<input type="submit" name="titles" value="Save changes" class="btn btn-primary" />
	<a href="<?= $SYS_pageroot ?>migrate-step3-doTitles.php" class="btn">Cancel</a>
<?php } ?>
</form>

<?php } ?>
<?php } /* QS do = save */ ?>
	</div>
</div>


<?php require('_footer.php'); ?>
