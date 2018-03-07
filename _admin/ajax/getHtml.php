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
		$field = formget("field");

		// If no errors:
		if ( $PAGE_dbid > 0 ) {

			$result = db_getHtmlFromPage( array(
							'site' => $PAGE_siteid,
							'id' => $PAGE_dbid
						) );

			// If anything was found, put it into pur PAGE_form
			if (!is_null($result))
			{
				$row = $result->fetch_object();

				if ( $field === "full" ) {
					echo $row->html;
				} else {
					echo $row->content;
				}

			} else {
				echo "Couldn't find the page!";
			}

		}

	}

?>
