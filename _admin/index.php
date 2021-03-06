<?php
	/* Set up template variables */
	$PAGE_name = 'Login';
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'sign in to your account';
?>
<?php require('_global.php'); ?>
<?php
if (isset($_SESSION['id'])) {
	$PAGE_name = "You're in!";
	$PAGE_desc = 'access granted to M2WP';
}
?>
<?php require('_header.php'); ?>


	<?php

		if (ISPOST)
		{
			$formMail = formGet('cms_mail');
			$formPass = formGet('cms_pass');

			$result = db2_getUserLoginInfo( array('mail' => $formMail) );

			if (!is_null($result))
			{

				$row = $result->fetch_object();
				$dbPass = $row->password;
				//$dbPass = 'hej';

				if ($formPass === $dbPass )
				{
					$loggedin = true;

					$_SESSION['username'] = $row->username;
					$_SESSION['password'] = $row->password;
					$_SESSION['mail'] = $row->mail;
					$_SESSION['level'] = $row->level;
					$_SESSION['id'] = $row->id;

					ob_clean();
					header('Location: ' . $SYS_pageroot);

				} else {

					$loggedin = false;

				}
			}
			else
			{
				$loggedin = false;
			}

			if (!$loggedin) {
				pushError("Login failed. Wrong Email-address or wrong password maybe? Remember that the password-field is case-sensitive!");
			}
		}
		else if ( isset($_GET['do']))
		{
			if ($_GET['do'] === 'logout') {

				unset( $_SESSION['username'] );
				unset( $_SESSION['password'] );
				unset( $_SESSION['mail'] );
				unset( $_SESSION['level'] );
				unset( $_SESSION['id'] );

				ob_clean();
				header('Location: ' . $SYS_pageroot);
			}
		}
	?>

	<?php
		outputErrors($SYS_errors);
	?>

	<div class="row">
		<div class="span12">

			<?php
				if (!isset($_SESSION['id'])) {
			?>

			<form class="well form-inline" action="" method="post">

				<input type="text" name="cms_mail" class="input-large" placeholder="E-mail" autocomplete="false" />
				<input type="password" name="cms_pass" class="input-medium" placeholder="Password" autocomplete="false" />

				<button type="submit" class="btn">Sign in</button>

			</form>

			<?php
				} else {
			?>

			<p>
				Let's get started migrating your old static sites!
			</p>
			<p>
				Head to the <a href="<?= $SYS_pageroot ?>project.php">"Projects"-section</a> to create and manage your projects.
			</p>
			<p>
				Or, go straight to the <a href="<?= $SYS_pageroot ?>migrate-select.php">"Migrate"-section</a> to work with existing migration data!
			</p>

			<p><em>Signed in as <strong><?= $_SESSION['mail'] ?></strong> - <a href="?do=logout">Sign out</a></em></p>

			<?php
				}
			?>

		</div>
	</div>


<?php require('_footer.php'); ?>
