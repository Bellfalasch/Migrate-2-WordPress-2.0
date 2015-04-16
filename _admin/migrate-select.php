<?php
	/* Set up template variables */
	$PAGE_name  = 'Step 0';
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'start your migration journey';
?>
<?php require('_global.php'); ?>
<?php require('_header.php'); ?>


	<div class="row">
		<div class="span7">
			<p>
				Select which of your projects you wanna work on in the migration process.
			</p>
		</div>

		<div class="span4 offset1">

			<h4>Need more projects?</h4>
			<p>Just go to the <a href="<?= $SYS_pageroot ?>project.php">Projects-page</a> and create some more then =)</p>

		</div>
	</div>

	<p>&nbsp;</p>

	<div class="row">
		<div class="span12">

			<?php
				$result = db_getSites();

				if (!is_null($result))
				{
			?>

			<ul class="thumbnails">

				<?php
					while ( $row = $result->fetch_object() )
					{
				?>

				<li class="span4">
					<div class="thumbnail">
						<h3>
							<a href="<?= $SYS_pageself ?>?project=<?= $row->id ?>"><?= $row->name ?></a>
						</h3>
						<em><?= $row->url ?></em>
						<p>
							Step: <span class="badge badge-inverse"><?= $row->step ?></span><br />
							Pages: <span class="badge badge-inverse"><?= $row->pages ?></span>
						</p>
					</div>
				</li>

				<?php
					}
				?>

			</ul>
			
			<?php
				}
				else
				{
					echo "<p>No projects found (<a href=\"" . $SYS_pageroot . "project.php\">create one</a>!)</p>";
				}
			?>

		</div>
	</div>


<?php require('_footer.php'); ?>