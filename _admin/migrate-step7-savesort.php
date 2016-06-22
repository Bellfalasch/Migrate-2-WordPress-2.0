<?php
	header('content-type: text/html; charset: utf-8'); 
	/* PAGE CALLED WITH AJAX - ONLY */
?>
<?php require('_global.php'); ?>
<?php

	if (ISPOST)
	{
		$order = urldecode( formget("order") );
//		pushError("Debugging");

		if (empty($SYS_errors)) {

			$orderArray = explode('|', $order);

			// Loop through each element in array, make sure it is only numbers, else skip ("ctype_digit" does this magic)
			$sanitizedValues = array_filter($orderArray, 'ctype_digit');

			// TODO:
			// Should we for each page double check that it exists and then update them one and one? Very very slow though on huge sites =/ But more "perfect" in that it would handle bad data better. Low prio now that I think about it.

			for ($i = 0; $i < count($sanitizedValues); $i++) { 

				$newSort = $i + 1;

				$result = db_setNewPageOrder( array(
							'id' => $sanitizedValues[$i],
							'sort' => $newSort,
							'site' => $PAGE_siteid
						) );

				if ($result >= 0) {
					echo "Page ID " . $sanitizedValues[$i] . " got sort value " . $newSort . "\n";
				} else {
					echo "Couldn't save!\n";
				}
			}

		}
	}

?>
