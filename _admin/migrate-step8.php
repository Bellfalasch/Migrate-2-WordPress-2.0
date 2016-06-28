<?php
	/* Set up template variables */
	$PAGE_step  = 8;
	$PAGE_name  = 'Step ' . $PAGE_step;
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'finalize your content before exporting it';
?>
<?php require('_global.php'); ?>
<?php include('_header.php'); ?>


<?php

// The actual code
// ****************************************************************************

	if (ISPOST)
	{

		// Get all pages that has been connected to a Wordpress page, these will get transfered now
		$result = db_getContentDataFromSite( array( 'site' => $PAGE_siteid ) );
		if ( isset( $result ) )
		{

			while ( $row = $result->fetch_object() )
			{

				$stop = false;

				// Waterfall-choose the best (cleanest) html from the database depending on which is available
				if ( !is_null($row->ready) ) {

					$content = $row->ready;

				} elseif ( !is_null($row->clean) ) {

					$content = $row->clean;

				} elseif ( !is_null($row->tidy) ) {

					$content = $row->tidy;

				} elseif ( !is_null($row->wash) ) {

					$content = $row->wash;

				} elseif ( !is_null($row->content) ) {

					$content = $row->content;

				} else {

					$stop = true;

				}

				if ( !$stop ) {

					// Tag code that will stick out a bit in Wordpress admin afterwards so you manually can validate everything easier
					if ( formGet("fix") === "yes" ) {
						$content = str_replace('<img ', '<img class="imgfix" ', $content);
						$content = str_replace('<a href="', '<a class="fix" href="', $content);
					}

					// Add the review page flag
					if ( formGet("flag") === "yes" ) {

						$content = "<div class=\"infobox warning\"><p>This content needs to be reviewed manually before publishing (after that, remove this box!)</p></div>" . $content;
					}

//						echo "<p>";
					echo "<strong>Updating:</strong> \"<span class=\"text-info\">" . str_replace( $PAGE_siteurl, "/", $row->page ) . "</span>\"";
//						echo " <strong>to Wordpress page:</strong> \"" . str_replace( $PAGE_sitenewurl, "/", $newlink ) . "\"";
					echo " <span class=\"label label-success\">OK</span>";
//						echo "</p>";
//						echo "<br />";

					// Remove all images
					if ( formGet("remove_img") === "yes" ) {
						$content = preg_replace( '/<img .*?\/?>/is', '', $content, -1, $count ); // "s" = If this modifier is set, a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
																								 // "i" = Match upper and lower case!
						echo " (<strong>" . $count . "</strong> images removed.) ";
					}
						if (formGet("save_finalize") != "Test Finalize") {

							echo " <strong>Result:</strong> <span class=\"label label-success\">Saved</span>";

							// Do some saving
							db_setReadyCode( array(
								'ready' => $content,
								'id' => $row->id
							) );

						} else {

							echo " <strong>Result:</strong> <span class=\"label label-important\">Not saved</span>";

						}

						echo "<br />";

//					}

				}

			}

		}

		echo "
			<br />
			<p>
				<strong>Now updating all the old links on all the pages:</strong>
			</p>";

		// Now run another batch updater that will fix all the old links between the pages so they're correct
		$result = db_getContentDataFromSite( array( 'site' => $PAGE_siteid ) );
		if ( isset( $result ) )
		{

			while ( $row = $result->fetch_object() )
			{

				// Don't bother updating links to pages this tool created as there can't be any links to them yet
				if ( $row->crawled == 1 ) {

					// Update all links
					$newlink = $row->page_slug;
					$oldlink = $row->page;

					if ($newlink != "" && !is_null($newlink))
					{

						$oldlink = str_replace( $PAGE_siteurl, "", $oldlink ); // Remove base URL from page URL

						$mapparArr = explode('/', $oldlink);
						$fil = $mapparArr[count($mapparArr) - 1];
						//$mapp = $mapparArr[count($mapparArr) - 2];

						// Add parents slug to root URL if this is a child page
						if ( $row->page_parent > 0 ) {
							$newlink = $row->parent_slug . "/" . $newlink;
						}

						// Re-build the full new URL for the page
						$separator = "/";
						if ( mb_substr($newlink,1) == "/" || mb_substr($PAGE_sitenewurl,-1) == "/" ) {
							$separator = "";
						}
						$newlink = $PAGE_sitenewurl . $separator . $newlink;
	/*
	TODO: Get this working?
						// Replace all the old href URLs with the new one in the current text
						$content = str_replace( " href=\"" . $fil, " href=\"" . $newlink, $content, $counter );
	*/

						if (formGet("save_finalize") != "Test Finalize") {

							// Update all the Links on ALL the pages on this site
							$fixLinks = db_updateContentLinks( array(
												'oldlink' => ' href="' . $oldlink,
												'newlink' => ' href="' . $newlink,
												'site' => $PAGE_siteid
										) );

						} else {
							$fixLinks = "???";
						}

						// Output a counter if we got any hits
						//echo "<strong>" . $oldlink . "</strong> removed <span class=\"badge badge-success\">" . $fixLinks . "</span> times<br />";
						//echo "<p>";
						echo "<strong>Update old links:</strong> \"" . str_replace( $PAGE_siteurl, "/", $oldlink ) . "\" ";
						echo "<strong>to Wordpress links:</strong> \"" . str_replace( $PAGE_sitenewurl, "/", $newlink ) . "\" ";
						echo "<span class=\"label label-success\">" . $fixLinks . "</span>";
						//echo "</p>";
						echo "<br />";

					}

				}

			}

			echo "<br />";

		}

		// Update Step data
		if (formGet("save_finalize") != "Test Finalize") {

			db_updateStepValue( array(
				'step' => $PAGE_step,
				'id' => $PAGE_siteid
			) );

		}

	}

?>

<form class="well form" action="" method="post">

	<div class="row">
		<div class="span11">

			<p>
				This is the end, the last step before we'll hand you that final XML export that you can import into WordPress.
			</p>
			<p>
				This step will run through your old pages now clean code and fix all those last little details that
				needs to be addressed before handing you the export XML data. We'll update all inline links that have
				changed, and things like that.
			</p>

			<h3>Settings:</h3>
			<label>
				<input type="checkbox" name="fix" value="yes"<?php if ( formGet('fix') === "yes" ) { ?> checked="checked"<?php } ?> />
				Add the class "fix" to links and "imgfix" to images inside content (easily spot them in admin and on site if you style them)
				<span class="help-block">The class on links is removed on all links we can manage to update to the new correct links automatically.</span>
			</label>
			<label>
				<input type="checkbox" name="flag" value="yes"<?php if ( formGet('flag') === "yes" ) { ?> checked="checked"<?php } ?> />
				Add a "Text not manually checked" on top of every moved page in WordPress?
				<span class="help-block">This helps you to keep track if you're gonna manually edit all pages when they're in WordPress</span>
			</label>
			<label>
				<input type="checkbox" name="remove_img" value="yes"<?php if ( formGet('remove_img') === "yes" ) { ?> checked="checked"<?php } ?> />
				Remove all images from the code (you'll upload them again in WordPress anyway because of re-design)?
			</label>
			<br />

			<input type="submit" name="save_finalize" value="Save Finalize" class="btn btn-primary" />

			<input type="submit" name="save_finalize" value="Test Finalize" class="btn" />

		</div>
	</div>

</form>


<?php require('_footer.php'); ?>