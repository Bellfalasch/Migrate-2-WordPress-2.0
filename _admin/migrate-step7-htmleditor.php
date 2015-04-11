<?php
	/* Set up template variables */
	$PAGE_title = "HTML editor";
?>
<?php require('_global.php'); ?>
<?php

	// Form generator
	addField( array(
		"label" => "HTML source:",
		"id" => "html",
		"type" => "area(6*13)",
		"description" => "Change the washed and cleaned HTML. Don't forget to save!",
		"min" => "1",
		"errors" => array(
						"min" => "Please keep number of character's on at least [MIN].",
					)
	) );

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf8" />
	<title><?= $PAGE_title ?> - Migrate 2 WordPress, 2.0 BETA</title>
	<!--<link rel="shortcut icon" href="<?= $SYS_root ?>/favicon.ico">-->
	<link rel="stylesheet" href="<?= $SYS_root . $SYS_folder ?>/assets/bootstrap.min.css" />
	<link rel="stylesheet" href="<?= $SYS_root . $SYS_folder ?>/assets/admin.css?v=<?php if (DEV_ENV) echo rand(); ?>" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
	<script src="<?= $SYS_root . $SYS_folder ?>/assets/bootstrap.min.js"></script>
	<script src="<?= $SYS_root . $SYS_folder ?>/assets/admin.js"></script>
</head>
<body class="<?= $SYS_script ?>">

<?php

	// Now that we are just before the form starts, we can output any errors we might have pushed into the error-array.
	// Calling this function outputs every error, earlier pushes to the error-array also stops the saving of the form.

	outputErrors($SYS_errors);

?>

	<?php if ( $PAGE_dbid > 0 ) { ?>

<form class="well form" action="" method="post">

	<?php

		// This is the output area, where all the field's html should be generated for empty field's SQL inserts, and already filled in field's SQL updates.
		// The fields data/content is generated in the upper parts of this document. Just call this function to get the html out.

		outputFormFields();

	?>

</form>

	<?php } ?>


<?php require('_footer.php'); ?>
