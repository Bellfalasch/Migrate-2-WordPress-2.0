<?php
	header('content-type: text/html; charset: utf-8'); 
	/* PAGE CALLED WITH AJAX - ONLY */
?>
<?php require('_global.php'); ?>
<?php

	////////////////////////////////////////////////////////
	// HANDLE POST AND SAVE CHANGES

	if (ISPOST)
	{
		$parent = formget("parent");
		$child = formget("child");
		$undo = formget("undo");

		// Secure/validate data
		if ( $undo == "false" ) {
			$undo = "false";
		} else {
			$undo = "true";
			$parent = 0;
		}

		// If no errors:
		if (empty($SYS_errors)) {

			// Fetch child page data (so we know it exists)
			$result = db_getPageTitleSlug( array(
							'site' => $PAGE_siteid,
							'id' => $child
						) );

			if (!is_null($result))
			{
				$row = $result->fetch_object();

				$slug = $row->page_slug;

				// Call function in "_database.php" that does the db-handling, send in an array with data
				$result = db_setPageAsChild( array(
							'id' => $child,
							'parent' => $parent,
							'site' => $PAGE_siteid
						) );

				// This is the result from the db-handling in my files.
				// (On update they return -1 on error, and 0 on "no new text added, but the SQL worked", and > 0 for the updated posts id.)
				if ($result >= 0) {
					echo "Done!";
				} else {
					echo "Couldn't save!";
				}

			} else {
				echo "Couldn't find the page!";
			}

		}

	}

?>
