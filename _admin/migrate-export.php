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
			$date = new DateTime();
			$now = time();

			// Enable function calls in Heredocs
			// http://stackoverflow.com/questions/104516/calling-php-functions-within-heredoc-strings
			function fn($data) {
				return $data;
			}
			$fn = 'fn';

			// Define XML export header as a Heredoc
			$XML_header = <<< EOT
<?xml version="1.0" encoding="UTF-8" ?>
<!-- This is a WordPress eXtended RSS file generated by WordPress as an export of your site. -->
<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
<!-- You may use this file to transfer that content from one site to another. -->
<!-- This file is not intended to serve as a complete backup of your site. -->

<!-- generator="Migrate 2 WordPress" created="{$date->format('Y-m-d H:i')}" -->
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.2/"
>

<channel>
	<title>$PAGE_sitename</title>
	<link>$PAGE_sitenewurl</link>
	<description>No description</description>
	<pubDate>{$fn( date(DATE_RSS, $now) )}</pubDate><!-- DONE? -->
	<language>en-US</language><!-- TODO -->
	<wp:wxr_version>1.2</wp:wxr_version>
	<wp:base_site_url>http://guide.ffuniverse.nu/</wp:base_site_url><!-- TODO -->
	<wp:base_blog_url>$PAGE_sitenewurl</wp:base_blog_url>

	<wp:author>
		<wp:author_id>1</wp:author_id>
		<wp:author_login>admin</wp:author_login>
		<wp:author_email>admin@example.com</wp:author_email>
		<wp:author_display_name><![CDATA[Migrate 2 WordPress]]></wp:author_display_name>
		<wp:author_first_name><![CDATA[Migrate 2 WordPress]]></wp:author_first_name>
		<wp:author_last_name><![CDATA[]]></wp:author_last_name>
	</wp:author>

	<wp:category><!-- TODO - Remove? -->
		<wp:term_id>1</wp:term_id>
		<wp:category_nicename>uncategorized</wp:category_nicename>
		<wp:category_parent></wp:category_parent>
		<wp:cat_name><![CDATA[Uncategorized]]></wp:cat_name>
	</wp:category>
	<wp:category>
		<wp:term_id>2</wp:term_id>
		<wp:category_nicename>uppdatering</wp:category_nicename>
		<wp:category_parent></wp:category_parent>
		<wp:cat_name><![CDATA[Uppdatering]]></wp:cat_name>
	</wp:category>
	<wp:category>
		<wp:term_id>3</wp:term_id>
		<wp:category_nicename>story</wp:category_nicename>
		<wp:category_parent></wp:category_parent>
		<wp:cat_name><![CDATA[Story]]></wp:cat_name>
	</wp:category>
	<wp:category>
		<wp:term_id>4</wp:term_id>
		<wp:category_nicename>disclaimer</wp:category_nicename>
		<wp:category_parent></wp:category_parent>
		<wp:cat_name><![CDATA[Disclaimer]]></wp:cat_name>
	</wp:category>

	<generator>https://github.com/Bellfalasch/Migrate-2-WordPress-2.0/</generator>
EOT;

			// Define XML export footer as a Heredoc
			$XML_footer = <<< EOT
</channel>
</rss>
EOT;

			// Reference: http://devtidbits.com/2011/03/16/the-wordpress-extended-rss-wxr-exportimport-xml-document-format-decoded-and-explained/

			$XML_content = <<< EOT

	<item>
		<title>|||TITLE|||</title>
		<link>|||URL|||</link>
		<pubDate>{$fn( date(DATE_RSS, $now) )}</pubDate><!-- DONE? -->
		<dc:creator><![CDATA[admin]]></dc:creator><!-- DONE? -->
		<guid isPermaLink="false">|||URL|||</guid>
		<description></description>
		<content:encoded><![CDATA[|||CONTENT|||]]></content:encoded>
		<excerpt:encoded><![CDATA[]]></excerpt:encoded>
		<wp:post_id>|||ID|||</wp:post_id><!-- DONE? -->
		<wp:post_date>{$date->format('Y-m-d H:i:s')}</wp:post_date><!-- DONE? -->
		<wp:post_date_gmt>{$date->format('Y-m-d H:i:s')}</wp:post_date_gmt><!-- DONE? -->
		<wp:comment_status>closed</wp:comment_status>
		<wp:ping_status>closed</wp:ping_status>
		<wp:post_name>|||SLUG|||</wp:post_name>
		<wp:status>|||STATUS|||</wp:status><!-- DONE? -->
		<wp:post_parent>|||PARENT|||</wp:post_parent>
		<wp:menu_order>|||I|||</wp:menu_order><!-- DONE? -->
		<wp:post_type>page</wp:post_type>
		<wp:post_password></wp:post_password>
		<wp:is_sticky>0</wp:is_sticky>
	</item>
EOT;

			$XML_header = htmlspecialchars($XML_header, ENT_QUOTES, "UTF-8");
			$XML_footer = htmlspecialchars($XML_footer, ENT_QUOTES, "UTF-8");

			echo "<pre>" . $XML_header;

			// Variable to store the entire XML file for later download
			$XML_file = $XML_header;
			$i = 0;

			while ( $row = $result->fetch_object() )
			{

				$stop = false;
				$i++;

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

					//Calculate the URL for every content
					$newlink = $row->page_slug;

					// Add parents slug to root URL if this is a child page
					if ( $row->page_parent > 0 ) {
						$newlink = $row->parent_slug . "/" . $newlink;
					}

					// Re-build the full new URL for the page
					$separator = "/";
					if ( mb_substr($newlink,1) == "/" || mb_substr($PAGE_sitenewurl,-1) == "/" ) {
						$separator = "";
					}
					$newlink = $PAGE_sitenewurl . $separator . $newlink;

					// Add trailing / if there is none
					if ( mb_substr($newlink,-1) != "/" ) {
						$newlink = $newlink . "/";
					}

					$title = $row->title;
					$slug = $row->page_slug;
					$id = $row->id;
					$parent = $row->page_parent;
					$status = "publish";

					// FFU specific: title starts with --- or === then don't publish this post
					if ( mb_substr( $title, 0, 3) == "===" || mb_substr( $title, 0, 3) == "---" ) {
						$status = "draft";
					}

					$this_content = $XML_content;
					$this_content = str_replace("|||URL|||",     $newlink, $this_content);
					$this_content = str_replace("|||TITLE|||",   $title,   $this_content);
					$this_content = str_replace("|||SLUG|||",    $slug,    $this_content);
					$this_content = str_replace("|||CONTENT|||", $content, $this_content);
					$this_content = str_replace("|||ID|||",      $id,      $this_content);
					$this_content = str_replace("|||PARENT|||",  $parent,  $this_content);
					$this_content = str_replace("|||I|||",       $i,       $this_content);
					$this_content = str_replace("|||STATUS|||",  $status,  $this_content);

					$this_content = htmlspecialchars($this_content, ENT_QUOTES, "UTF-8");

					echo $this_content;

					$XML_file .= $this_content;

				}

			}

			echo $XML_footer . "</pre>";

			$XML_file .= $XML_footer;

/*
Test:
			echo "<pre>$XML_header
$XML_content
$XML_footer</pre>";
*/

			// Uncomment to store the code as an actual xml-file on the server
			//file_put_contents("site" . $PAGE_siteid . "_export.xml", $string);

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

				<p>
					To import this information into a WordPress site follow these steps:
				</p>
				<ol>
					<li>Log in to that site as an administrator.</li>
					<li>Go to Tools: Import in the WordPress admin panel.</li>
					<li>Install the "WordPress" importer from the list.</li>
					<li>Activate &amp; Run Importer.</li>
					<li>Upload this file using the form provided on that page.</li>
					<li>You will first be asked to map the authors in this export file to users on the site. For each author, you may choose to map to an existing user on the site or to create a new user.</li>
					<li>WordPress will then import each of the posts, pages, comments, categories, etc. contained in this file into your site.</li>
				<ol>

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
