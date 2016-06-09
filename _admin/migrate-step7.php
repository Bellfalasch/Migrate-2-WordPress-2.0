<?php
	/* Set up template variables */
	$PAGE_step  = 7;
	$PAGE_name  = 'Step ' . $PAGE_step;
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'administrate your page structure';
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
		
		$slug = fn_getSlugFromTitle($formTitle);

		// If no errors:
		if (empty($SYS_errors)) {

			// Call insert-function from our database-file for admin.
			$result = db_setPageSimple( array(
						'title' => $formTitle,
						'site' => $PAGE_siteid,
						'slug' => $slug
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
			<h2>Structure pages</h2>
			<p>
				Now's the chance to fine tune your page's HTML in a quick and easy way. You can also create new pages, if that's something you'd want. Should you regret any of the pages here you can of course also delete them.
			</p>
			<p>
				<strong>Sorting:</strong>
				Hit the big "Sort" button to activate sorting of pages. This is easy and intuitive, just drag and drop all pages into the order you'd like. To make sorting easier for you we hide all other buttons. Just remember to hit the "Save new order"-button when done sorting so that it all is saved into the database before next step.
			</p>

			<div class="alert alert-block alert-success">
				<h4>No Save-button!?</h4>
				<p>
					When you're ready with your page structure, manually <a href="<?= $SYS_pageroot ?>migrate-step8.php">go to Step 8</a>.
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
		<table>
			<thead>
				<th>Sort</th>
				<th>-</th>
				<th>Title</th>
				<th>Slug</th>
				<th>URL</th>
				<th>-</th>
			</thead>
			<tbody id="pageTableBody">

<?php

		while ( $row = $result->fetch_object() )
		{
			$addclass = "rows";

			// Add child-class to children so you see it visually
			if ( $row->page_parent > 0 ) {
				$addclass = " child";
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

			// Title is not set in the database
			if ( is_null($title) ) {
				$title = "<em>- Unknown -</em>";
			}

			echo "<td class=\"move-handle\"><span class=\"icon-move\" aria-hidden=\"true\"></span></td>";

			echo "<td class=\"centered\"><a href=\"" . $SYS_pageroot . "migrate-step7-htmleditor.php?id=" . $row->id . "\" class=\"btn btn-mini btn-primary\" data-title=\"" . $title . "\" data-toggle=\"modal\" data-target=\"#html-modal\">Edit HTML</a></td>";

			echo "<td>" . $title . "</td>";
			echo "<td>" . $row->page_slug . "</td>";
			echo "<td>";

			if ( $row->crawled == 1 ) {
				echo "<a href=\"" . $page . "\" target=\"_blank\" title=\"Click to open the original crawled page\">";
				echo $url;
				echo "</a>";
			} else {
				echo $url;
			}

			echo "</td>";
			echo "<td class=\"centered\"><a href=\"" . $SYS_pageself . "?del=" . $row->id . "\" class=\"btn btn-mini btn-danger\">Delete</a></td>";
			echo '</tr>';
		}

?>
			</tbody>
		</table>

		<div id="pageTable2">
			<div class="rows">
				<span class="move-handle">Drag me 1</span>
				<span>Don't drag me 1</span>
			</div>
			<div class="rows">
				<span class="move-handle">Drag me 2</span>
				<span>Don't drag me 2</span>
			</div>
			<div class="rows">
				<span class="move-handle">Drag me 3</span>
				<span>Don't drag me 3</span>
			</div>
		</div>

		<!-- Modal -->
		<div id="html-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 id="myModalLabel">Edit page HTML source</h4>
			</div>
			<div class="modal-body">
				<p>One fine body this is ... so sad it will be replaced.</p>
			</div>
			<div class="modal-footer">
				<p class="pull-left"><strong>Don't forget to save changes!</strong> (Use the save button above)</p>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>

<?php

	}

?>

		</div>
	</div>


<?php require('_footer.php'); ?>
