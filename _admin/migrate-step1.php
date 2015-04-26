<?php
	// This page will take a looong time to finish, so remove any timeouts on the server
	set_time_limit(0);
	ini_set('max_execution_time', 0);

	/* Set up template variables */
	$PAGE_step  = 1;
	$PAGE_name  = 'Step ' . $PAGE_step;
	$PAGE_title = 'Admin/' . $PAGE_name;
	$PAGE_desc  = 'crawl an entire site\'s html';
?>
<?php require('_global.php'); ?>
<?php include('_header.php'); ?>


	<?php
		outputErrors($SYS_errors);
	?>


	<div class="row">
		<div class="span7">

<?php

// Crawler setup
// ****************************************************************************

	// Code made by epaaj at ninjaloot.se!
	// Modifications by Bellfalasch

	$check_links = array();
	$check_links[$PAGE_siteurl] = 0;
	$checked_link = "";

	// At the moment only way to delete data in the table and start anew:
	//mysql_query("TRUNCATE `" . $cleaner_table . "`");

	// List of file endings on pages to crawl for, fetch from setting
	// Our formGet doesn't tackle post arrays, so need to read it directly
	$fileendings = array();

	if ( isset($_POST['filetype']) ) {
		$fileendings = $_POST['filetype'];
	}

	// Custom debugging of crawl activated
	$debugger = array();
	if ( formGet('debug') === 'yes' ) {
		DEFINE('DEBUG', true);
	} else {
		DEFINE('DEBUG', false);
	}


// Crawler functions
// ****************************************************************************

// Simple insert into the database, no check if data already is there.
function savepage($url, $html, $title)
{
	global $PAGE_siteid;

	if ( mb_detect_encoding($html, "utf-8, iso-8859-1") == "UTF-8" ) {
		$html;
	} else {
		$html = iconv("iso-8859-1", "utf-8", $html);
	}

//	echo mb_detect_encoding($html, "utf-8, iso-8859-1");

	$slug = fn_getSlugFromTitle( $title );

	if ($html != "") {

		// Check if page exists
		$exists = db_getDoesPageExist( array(
						'site' => $PAGE_siteid,
						'page' => $url
					) );

		// Insert or Update?
		if ( isset($exists) ) {

			$row = $exists->fetch_object();

			$result = db_setUpdatePage( array(
							'html' => $html,
							'id' => $row->id,
							'page_slug' => $slug,
							'title' => $title
						) );

		} else {

			$result = db_setNewPage( array(
							'site' => $PAGE_siteid,
							'html' => $html,
							'page' => $url,
							'content' => NULL,
							'page_slug' => $slug,
							'title' => $title
						) );
		}

	}
}

function checklink($link)
{
	global $checked_link;

	// Find every space in URLs, and replace it with %20
	$link = str_replace( " ", "%20", $link );

	// Find all achors ( #-sign ) and delete it and everything after
	$matches = array();
	$anchor_search = "/(.*?)#(.*?)/i";
	preg_match($anchor_search, $link, $matches);

	// If match found (# found in URL) remove it
	if ( $matches ) {
		$link = $matches[0];
	}

	if ( validfiletype($link) ) {
		$checked_link = $link;

//		array_push( $debugger, "Valid 'checked_link': " . $checked_link );
		// Note to self: don't debug here, will come out double since the crawler code does all the debugging.

		return true;
	} else {
		return false;
	}

}

// Validate file ending (don't add links to files we don't want)
function validfiletype($link)
{
	global $fileendings;

	$filetype = explode(".", $link);
	$filetype = $filetype[sizeof($filetype)-1];
	$filetype = explode("?", $filetype);
	$filetype = $filetype[0];

	if (in_array($filetype, $fileendings) ) {
		return true;
	} else {
		return false;
	}

}

function forsites($check_links)
{
	global $PAGE_siteurl;
	global $check_links;
	global $checked_link;

	$continue = true;

	while($continue)
	{
		$continue = false;

		foreach ($check_links as $url => $v)
		{
			if ($v == 0)
			{
				getsite($url);
				$continue = true;
			}
		}
	}
}

// Request the site we want to crawl
function getsite($url)
{
	global $PAGE_siteurl;
	global $PAGE_step;
	global $PAGE_siteid;
	global $check_links;
	global $checked_link;
	global $debugger;

	$linklist = array(); // Array to store all the links in

	// Different kind of link formats for this site.
	// Example from one of my old sites that had it's navigation in a select > option-list ... >_<
	$search = array (
		'/\<option value="(.*?)".*>.*<\/option>/i',
		'/ href="(.*?)"/i',
		'/ src="(.*?)"/i',
		'/window\.open\("(.*?)"/i'
	);
	$search_length = count($search);

/*
	'/src="([^\s"]+)"/iU',
	'/\<a href="(.*?)"(.*?)>(.*?)<\/a>/i',
	'/\<frame src="(.*?)"(.*?)/i',
	'/\<a(.*)href="(.*?)"(.*?)>(.*?)<\/a>/i',
	'/\<A HREF="(.*?)"(.*?)>(.|\n)+(.*?)(.|\n)+<\/A>/i',
	'/<a\s[^>]*href=([\"\']??)([^\\1 >]*?)\\1[^>]*>(.*)<\/a>/siU',
	'/\<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU',
*/
	// Need help? Check this awesome guide: http://www.the-art-of-web.com/php/parse-links/
	// http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page
	// http://www.catswhocode.com/blog/15-php-regular-expressions-for-web-developers

	echo "<p>";
	echo "<strong>Fetching URL:</strong> " . $url . " ";

	// Get a URL's code
	$http_request = fopen($url, "r");

	// Check HTTP status message, only get OK pages (if setting says so)
	if ($http_request)
	{
		// Check that it says status 200 OK in the header
		if (is_array($http_response_header)) {
			if ( in_array( substr($http_response_header[0],9,1), array("2","3") ) && substr($http_response_header[4],10,12) != "/_error.aspx" || formGet("header") == "" ) {
				echo "<span class=\"label label-success\">OK</span>";
			} else {
				echo "<span class=\"label label-important\">HTTP ERROR</span>";
				$check_links[$url] = 2;
				$search = "";
			}
		} else {
			echo "<span class=\"label label-important\">HTTP ERROR</span>";
			$check_links[$url] = 2;
			$search = "";
		}
	}
	else
	{
		echo "<span class=\"label label-important\">HTTP ERROR</span>";
		$check_links[$url] = 2;
		$search = "";
	}

	echo "</p>";

	//$http_request = stream_get_contents($http_request);

	// Collect a list of links from our pages and check for duplicates
	$pagebuffer = "";

	if ($search_length > 0) { // If we have any search terms

		while ( ($buffer = fgets($http_request)) !== false )
		{

			$pagebuffer .= $buffer; // "while" checks if it worked, add it to the buffer (no idea why it adds it like this)
									// Nevermind, I read up on documentation ... fgets apparently reads files/URLs line by line (facepalm)

		}

	}

	// Search for all the different regex we have
	for ( $i = 0; $i < $search_length; $i++ )
	{
		// Find all matching links in the fetched URL's html
		if ( preg_match_all($search[$i], $pagebuffer, $result) )
		{

			array_push( $debugger, '<strong>$result</strong><pre>' . var_export($result, true) . "</pre>" );

			// Add each link we find to our link list
			$result_length = count($result[1]);

			array_push( $debugger, "Array length: " . $result_length . "<br />" );

			for ( $u = 0; $u < $result_length; $u++ )
			{

				array_push( $debugger, '<strong>' . $u . '</strong> - ' . in_array($result[1][$u], $linklist) . ' ' );

				// Don't add duplicates
				if ( !in_array($result[1][$u], $linklist) ) {

					array_push( $debugger, '<strong>$result[1][$u]:</strong> <span class="text-info">' . var_export($result[1][$u], true) . '</span> ' );
					array_push( $debugger, '<strong>validfiletype():</strong> ' . var_export(validfiletype($result[1][$u]), true) . '<br />' );

					if ( validfiletype($result[1][$u]) ) {

						array_push($linklist, $result[1][$u]); // Preg_match_all returns array like so:
															   // 0 = The matching strings (with href etc), and 1 = only the exact result matches

					}

				}

			}

			array_push( $debugger, '<strong>$linklist</strong><pre>' . var_export($linklist, true) . "</pre>" );

		}
	}
/*
	// Regexp-format on the URL's we'll primarily look for as invalid (not contained in that site).
	$search_links = array(
		'/^\.\.(.*?)/i',
		'/^http\:\/\/(.*?)/i'
	);
*/

	echo "<ol>";

	$links_length = count($linklist);

	// For each link found on this page ... analyze it!
	for ( $j = 0; $j < $links_length; $j++)
	{

		array_push( $debugger, "<strong>Validating link:</strong> <span class='text-info'>" . $linklist[$j] . "</span><br />" );

		if ( !empty($linklist[$j]) )
		{

			// Honeypot, catching bad URLs: (going down one folder)
			if (preg_match('/^\.\.(.*?)/i', $linklist[$j], $res_links))
			{

				array_push( $debugger, " = <span class='text-error'>not allowed</span>" );

			}
			// Honeypot, catching bad URLs: (http-links, most likely leaving the site but check and make sure)
			// Does the URL start with "http://"? (or https://)
			else if (preg_match('/^http[s]?\:\/\/(.*?)/i', $linklist[$j], $res_links))
			{
				//$break = false;

				array_push( $debugger, " = http link, checking if correct domain ..." );

				if ( strlen($linklist[$j]) >= strlen($PAGE_siteurl) )
				{

					// Page URL is longer than site address, so we can do some checking here
/*
					if ( (($res_links[0][strlen($PAGE_siteurl)-1] != ".") ) )
					{
						for ($k=0; $k<strlen($PAGE_siteurl); $k++)
						{
							if ($res_links[0][$k] != $PAGE_siteurl[$k])
							{
								//echo $site_address[$k] . " <span class=\"label label-info\">Link</span><br />";
								$break = true;
								break;
*/
					// Check that we have the correct root URL
					if ( mb_substr( $linklist[$j], 0, strlen($PAGE_siteurl) ) == $PAGE_siteurl ) {
						array_push( $debugger, " = <span class='text-success'>cool, valid URL</span>" );

						// Only add it to the linklist if it isn't there already
						if (!array_key_exists($linklist[$j], $check_links))
						{
							array_push( $debugger, " Not added before" );
							// TODO: When I add this to the array we will end up in an eternal loop, if I don't add it we will stop after one page crawl
						//	$check_links[$linklist[$j]] = 0; // Is added to array-list and flagged as not crawled (will be crawled later)
						} else {
							array_push( $debugger, " Already added" );
						}
						//$break = true;
						//break;
						//continue;
/*
							}
						}
					}
*/
					}
					else
					{
						//$break = true;
						array_push( $debugger, " = <span class='text-error'>not correct URL base</span>" );
						//continue;
					}
				}
				else
				{
					//$break = true;
					array_push( $debugger, " = <span class='text-error'>URL not allowed</span>" );
					//continue;
				}

			}
			else // Link not starting with "../" or "http(s)://"
			{

				// Match link without regexp, should be valid and inside that site

				array_push( $debugger, " = not '..' or 'http' in start." );

				// Don't collect garbage links (only # in the href, or mailto-links)
				if ($linklist[$j] != "#" && substr( $linklist[$j], 0, 7 ) != "mailto:")
				{
					// Create full http links with domain name and all
					$link_full = $PAGE_siteurl . $linklist[$j];

					// Output information (link) to user
					echo "<li><a href=\"" . $link_full . "\" target=\"_blank\">" . $link_full . "</a>\n";

#						$link = preg_replace($replace_search, $replace, $link_full[1]);
					if (checklink($link_full))
					{
						//echo "\n" . $checked_link . " ---\n";
						if (!array_key_exists($link_full, $check_links))
						{
							echo " <span class=\"label label-info\">Added</span>";
							$check_links[$link_full] = 0; // Is added to array-list and flagged as not crawled (will be crawled later)

							//array_push( $debugger, " 'checked_link' (" . $checked_link . ") = 0. " );
							array_push( $debugger, " <strong>\"checklink\"</strong>: <span class='text-success'>Valid, added to list!</span>");

						} else {
							echo " <span class=\"label\">Skipped</span>";
							array_push( $debugger, " <strong>\"checklink\"</strong>: <span class='text-error'>Already in our list</span>");
						}
					} else {
						echo " <span class=\"label\">Not a page</span>";
					}
					echo "</li>";

				}

			}
		}

		array_push( $debugger, "<br />" );

	}

	echo "</ol>";

	$check_links[$url] = 1; // Link is flagged as parsed/crawled

	array_push( $debugger, "<br /><strong>'check_links' array:</strong><br /><pre>" . var_export($check_links, true) . "</pre>" );

	// Output everything we stored in the debugger array, if debugging is activated by user
	if (DEBUG) {
		foreach($debugger as $error) {
			echo $error;
		}
	}

	// Close file/URL
	fclose($http_request);

	echo "<span class=\"badge badge-inverse\">" . count($check_links) . "</span> unique links collected (so far)!";

	// Only save when Run crawl is pressed (never on Test)
	if (formGet("save_crawl") == "Run crawl") {

		$title = fn_getTitleFromUrl($url);

		savepage($url, trim($pagebuffer), $title );

	}

	echo "<br /><br />";

}

// Crawler, caller
// ****************************************************************************

	if (ISPOST)
	{

		forsites($check_links);
		#getsite($site, $site_address);

		//print_r($check_links);
		//echo count($check_links);

		// Don't save on test
		if (formGet("save_crawl") == "Run crawl") {

			db_updateStepValue( array(
				'step' => $PAGE_step,
				'id' => $PAGE_siteid
			) );

			echo "<p><strong>Result:</strong> <span class=\"label label-success\">Saved</span></p>";

		} else {
			echo "<p><strong>Result:</strong> <span class=\"label label-important\">Not saved</span></p>";
		}

	}

?>
		</div>

		<div class="span4 offset1">

			<h3>Crawling / scraping</h3>
			<p>
				What we'll do here is go to your start page (the one entered on the Project page) and find any
				link we can identify. We add all these links to a list of items to crawl. After the first page
				is crawled we go to the first link in the list we created. Here we do the same thing as before,
				we find all valid links on that page too but we only add it to the list if it's not already there.
				This way we never crawl a page twice! After all the links in the list have been crawled we're
				done here.
			</p>

			<h3>Legend</h3>
			<h4>Requesting a page:</h4>
			<p>
				<span class="label label-success">OK</span> - A link to a valid page has been successfully crawled.
			</p>
			<p>
				<span class="label label-important">HTTP ERROR</span> - The URL to be crawled didn't give a valid response,
				so it has been skipped. You'd better check this issue up manually.
			</p>
			<h4>Found links:</h4>
			<p>
				<span class="label label-info">Added</span> - Found a new link, it's added to the list of
				pages/links we will collect later.
			</p>
			<p>
				<span class="label">Skipped</span> - This link is already crawled, it will not be
				crawled again.
			</p>
			<p>
				<span class="label">Not a page</span> - This link is not a page with content (images or something similar), it will not be
				crawled.
			</p>
			<h4>Storing HTML:</h4>
			<p>
				<span class="label label-success">Saved</span> - All the found links have been crawled
				and the html saved to your database.
			</p>
			<p>
				<span class="label label-important">Not saved</span> - All the found links have been crawled,
				but none of them have been saved to your database. Click the "Run crawl"-button instead to
				save your data (existing data will be replaced!).
			</p>
		</div>
	</div>


<form class="well form" action="" method="post">

	<div class="row">
		<div class="span11">

			<p>
				The crawler will find any links on the URL you've set up on the settings page "Project" for this Project. Only valid links
				that are located in the same folder/root as the URL you gave will be fetched. After the first page is crawled, the crawler will
				continue to follow every link it can find.
			</p>
			<p>
				We will crawl <strong><?= $PAGE_siteurl ?></strong> for you and fetch all unique links there.
			</p>

			<div class="row">
				<div class="span5">
					<h4>Fetch these filetypes</h4>

					<?php
						// Valid file endings to crawl
						$optionArray = array("aspx","asp","htm","html","php");
						if (isset($_POST['filetype'])) {
							$optionArray = $_POST['filetype'];
						}
					?>
					<label><input type="checkbox" name="filetype[]" value="aspx"<?php if (in_array("aspx",$optionArray)) { ?> checked="checked"<?php } ?> /> aspx</label>
					<label><input type="checkbox" name="filetype[]" value="asp"<?php if (in_array("asp",$optionArray)) { ?> checked="checked"<?php } ?> /> asp</label>
					<label><input type="checkbox" name="filetype[]" value="html"<?php if (in_array("html",$optionArray)) { ?> checked="checked"<?php } ?> /> html</label>
					<label><input type="checkbox" name="filetype[]" value="htm"<?php if (in_array("htm",$optionArray)) { ?> checked="checked"<?php } ?> /> htm</label>
					<label><input type="checkbox" name="filetype[]" value="php"<?php if (in_array("php",$optionArray)) { ?> checked="checked"<?php } ?> /> php</label>
				</div>

				<div class="span5 offset1">
					<h4>Perform HTTP-status check</h4>
					<label><input type="checkbox" name="header" value="yes"<?php if (isset($_POST['header'])) { ?> checked="checked"<?php } ?> /> Yes! (skip all pages giving errors)</label>

					<br />
					<h4>Debug-mode</h4>
					<label>
						<input type="checkbox" name="debug" value="yes"<?php if (isset($_POST['debug'])) { ?> checked="checked"<?php } ?> />
						Active (output extra debug-information during crawl)
					</label>
				</div>
			</div>

			<p class="text-error">
				Crawling of a website can take a very long time, depending on how many pages and links it has.
			</p>

			<input type="submit" name="save_crawl" value="Run crawl" class="btn btn-primary" />

			<input type="submit" name="save_crawl" value="Test crawl" class="btn" />

		</div>
	</div>

</form>


<?php require('_footer.php'); ?>