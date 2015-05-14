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
		$PAGE_dbid = formget("id"); // page id
		$deleted = formget("delete"); // delete or undo

		// Only allow true or false here
		if ( $deleted === "true" ) {
			$deleted = 1;
		} else {
			$deleted = 0;
		}

		// If no errors:
		if ( $PAGE_dbid > 0 ) {

			// Fetch page data just to see that it exists
			$result = db_getPageTitleSlug( array(
							'id' => $PAGE_dbid,
							'site' => $PAGE_siteid
						) );

			// If anything was found, put it into pur PAGE_form
			if (!is_null($result))
			{
				$row = $result->fetch_object();

				$del = db_delPage( array(
							'id' => $PAGE_dbid,
							'site' => $PAGE_siteid,
							'deleted' => $deleted
						) );

				if ($del >= 0) {
					//fn_infobox("Delete successful", "The selected page has been deleted.",'');
					if ( $deleted === 1 ) {
						echo "Deleted!";
					} else {
						echo "Delete is undone";
					}
				} else {
					//pushError("Delete of page failed, please try again.");
					echo "Not possible to delete!";
				}

			} else {
				echo "Couldn't find the page!";
			}

		}

	}

?>
