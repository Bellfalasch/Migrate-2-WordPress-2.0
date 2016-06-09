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
/*
		$PAGE_dbid = formget("id");
		$type = urldecode( formget("type") );
		$value = urldecode( formget("value") );

		// Secure/validate data
		if ( $type == "slug" ) {
			$type = "slug";
		} else {
			$type = "title";
		}

		// Secure but allow " and ' in title
		$value = str_replace( "\"", "&quot;", $value );
		$value = str_replace( "'", "\'", $value );
		$value = str_replace( "\\", "\\\\", $value );

//		pushError("Debugging");

		// If no errors:
		if (empty($SYS_errors)) {

			// Fetch page data
			$result = db_getPageTitleSlug( array(
							'site' => $PAGE_siteid,
							'id' => $PAGE_dbid
						) );

			// If anything was found, put it into pur PAGE_form
			if (!is_null($result))
			{
				$row = $result->fetch_object();

				$slug = $row->page_slug;
				$title = $row->title;
				$crawled = $row->crawled; // Might use in future to also update/set URL (but why would we need that?)

				// Only update the value we sent in
				switch($type) {
					case "slug" :
						$slug = $value;
						break;
				
					case "title" :
						$title = $value;
						$slug = fn_getSlugFromTitle($title); // Calculate a new slug
						break;
				}

				// Call function in "_database.php" that does the db-handling, send in an array with data
				$result = db_setTitleAndSlug( array(
							'slug' => $slug,
							'title' => $title,
							'site' => $PAGE_siteid,
							'id' => $PAGE_dbid
						) );

				// This is the result from the db-handling in my files.
				// (On update they return -1 on error, and 0 on "no new text added, but the SQL worked", and > 0 for the updated posts id.)
				if ($result >= 0) {
					//fn_infobox("Save successful", "Data updated",'');
					echo $slug;
				} else {
					//pushError("Data could not be saved, do retry.");
					echo "Couldn't save!";
				}

			} else {
				//pushError("Couldn't find the requested page's HTML!");
				echo "Couldn't find the page!";
			}

		}
*/
	}

?>
