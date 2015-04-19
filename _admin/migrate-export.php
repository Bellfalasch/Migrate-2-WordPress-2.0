<?php
	/* Set up template variables */
	$PAGE_name  = 'Step X';
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc = 'download your export of content in the Wordpress-format';
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

					// TODO: Generate XML
					// TODO: Use variable $content

				}

			}

		}

	}

?>

	<form class="well form" action="" method="post">

		<div class="row">
			<div class="span11">

				<p>
					Download XML file with all your pages and content. It can easily be imported into any WordPress installation.
				</p>
				<p>
					Wanna change something? Just go back to the earlier steps and update your content and do another export.
				</p>

				<p class="text-center">
					<input type="submit" name="save_move" value="Download XML" class="btn btn-primary btn-large" />
				</p>

			</div>
		</div>

	</form>

	<h2>Recommended WordPress plugins</h2>
	<p>
		After you've added all your content to WordPress, a huge task remains: to get it production ready.
		To aid you in this task WordPress has many good plugins you can use. Here's a list of a few of them that
		I use to add thumbnails, change page templates, etc, in a jiffy.
	</p>

	<ul class="thumbnails">
		<li class="span4">
			<a href="#" class="thumbnail">
				<img src="" alt="Image" />
			</a>
			<h4>Thumbnail label</h4>
			<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
			<p class="text-center">
				<a href="#" class="btn btn-small" target="_blank">Check it out!</a>
			</p>
		</li>
		<li class="span4">
			<a href="#" class="thumbnail">
				<img src="" alt="Image" />
			</a>
			<h4>Thumbnail label</h4>
			<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
			<p class="text-center">
				<a href="#" class="btn btn-small" target="_blank">Check it out!</a>
			</p>
		</li>
		<li class="span4">
			<a href="#" class="thumbnail">
				<img src="" alt="Image" />
			</a>
			<h4>Thumbnail label</h4>
			<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
			<p class="text-center">
				<a href="#" class="btn btn-small" target="_blank">Check it out!</a>
			</p>
		</li>
		<li class="span4">
			<a href="#" class="thumbnail">
				<img src="" alt="Image" />
			</a>
			<h4>Thumbnail label</h4>
			<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
			<p class="text-center">
				<a href="#" class="btn btn-small" target="_blank">Check it out!</a>
			</p>
		</li>
		<li class="span4">
			<a href="#" class="thumbnail">
				<img src="" alt="Image" />
			</a>
			<h4>Thumbnail label</h4>
			<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
			<p class="text-center">
				<a href="#" class="btn btn-small" target="_blank">Check it out!</a>
			</p>
		</li>
		<li class="span4">
			<a href="#" class="thumbnail">
				<img src="" alt="Image" />
			</a>
			<h4>Thumbnail label</h4>
			<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
			<p class="text-center">
				<a href="#" class="btn btn-small" target="_blank">Check it out!</a>
			</p>
		</li>
	</ul>


<?php require('_footer.php'); ?>
