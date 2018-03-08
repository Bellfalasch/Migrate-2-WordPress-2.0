<?php
	header('content-type: text/html; charset: utf-8');
	/* PAGE CALLED WITH AJAX - ONLY */
?>
<?php require_once('../_global.php'); ?>
<?php

	////////////////////////////////////////////////////////
	// HANDLE POST AND SAVE CHANGES

	if (ISPOST)
	{
		$PAGE_dbid = formget("id"); // page id
		$type = formget("type");

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

				if ( $type === "full" ) {
					$html = $row->html;
				} else {
					$html = $row->content;
				}
				echo htmlentities( $html, ENT_COMPAT, 'UTF-8', false );

			} else {
				echo "Couldn't find the page!";
			}

		}

	}

?>
