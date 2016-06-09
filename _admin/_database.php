<?php

	// All these SQLs are for the different pages in this admin. Add yours here.

	//////////////////////////////////////////////////////////////////////////////////
	// STEPS
	//////////////////////////////////////////////////////////////////////////////////

	/* Universal SQL used in many places */
	/* **************************************************************************** */
	function db_updateStepValue($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_sites`
			SET `step` = {$in['step']}
			WHERE `id` = {$in['id']}
			AND
			(
				{$in['step']} > `step`
				OR
				0 = {$in['step']}
			)
		");
	}

	/* STEP 1 */
	/* **************************************************************************** */
	// Also used in Step 3
	function db_setNewPage($in) { cleanup($in);
		return db_MAIN("
			INSERT INTO `migrate_content`
				(`site`,`html`,`page`,`content`,`page_slug`,`page_parent`,`crawled`,`title`)
			VALUES(
				{$in['site']},
				{$in['html']},
				{$in['page']},
				{$in['content']},
				{$in['page_slug']},
				{$in['page_parent']},
				{$in['crawled']},
				{$in['title']}
			)
		");
	}
	// Check if crawled page exist already, so we won't create it again
	function db_getDoesPageExist($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			AND `page` = {$in['page']}
		");
	}
	// Crawled page existed, so update it's html instead of adding a new page
	function db_setUpdatePage($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `html` = {$in['html']},
			`page_slug` = {$in['page_slug']},
			`title` = {$in['title']}
			WHERE `id` = {$in['id']}
		");
	}

	/* STEP 2 */
	/* **************************************************************************** */
	function db_setContentCode($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `content` = {$in['content']}
			WHERE `id` = {$in['id']}
			LIMIT 1
		");
	}

	function db_getHtmlFromFirstpage($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `page`, `html`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			ORDER BY `id` ASC
			LIMIT 1
		");
	}

	function db_getDataFromSite($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `page`, `html`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			ORDER BY `page` ASC
		");
	}

	/* STEP 3 */
	/* **************************************************************************** */
	// Also used in Step 7
	function db_getHtmlFromPage($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `title`, `page`, `content`, `wash`, `tidy`, `clean`
			FROM `migrate_content`
			WHERE `id` = {$in['id']}
			AND `site` = {$in['site']}
			LIMIT 1
		");
	}
	// Also used in Step 7
	function db_getPagesFromSite($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `title`, `page`, `crawled`, `page_slug`, `page_parent`, `page_sort`, `deleted`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			ORDER BY
				CASE 
				WHEN `page_parent` = 0
				THEN
					CASE WHEN `page_sort` = 0 THEN `id` ELSE `page_sort` END
				ELSE `page_parent`
				END
			ASC, `id` ASC
		");
	}
	// Also used in Step 7
	function db_delPage($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `deleted` = {$in['deleted']}
			WHERE `site` = {$in['site']}
			AND `id` = {$in['id']}
			LIMIT 1
		");
	}

	function db_setDuplicatePage($in) { cleanup($in);
		return db_MAIN("
			INSERT INTO `migrate_content`
			( `page`, `title`, `crawled`, `html`, `site`, `content`, `wash`, `tidy`, `clean`, `ready`, `page_slug`, `page_parent` )
			SELECT CONCAT(`page`, '_2'), CONCAT(`title`, ' (2)'), 0, `html`, `site`, `content`, `wash`, `tidy`, `clean`, `ready`, CONCAT(`page_slug`, '_2'), `page_parent`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			AND `id` = {$in['id']}
		");
	}

	function db_getPageTitleSlug($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `title`, `page_slug`, `page`, `crawled`
			FROM `migrate_content`
			WHERE `id` = {$in['id']}
			AND `site` = {$in['site']}
			LIMIT 1
		");
	}

	function db_setTitleAndSlug($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `page_slug` = {$in['slug']},
				`title` = {$in['title']}
			WHERE `id` = {$in['id']}
			  AND `site` = {$in['site']}
			LIMIT 1
		");
	}

	function db_setPageAsChild($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `page_parent` = {$in['parent']}
			WHERE `id` = {$in['id']}
			  AND `site` = {$in['site']}
			LIMIT 1
		");
	}

	/* STEP 4 */
	/* **************************************************************************** */
	function db_setWashCode($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `wash` = {$in['wash']}
			WHERE `id` = {$in['id']}
			LIMIT 1
		");
	}

	// Also used in Step 5 and 6
	function db_getContentFromSite($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `page`, `content`, `wash`, `tidy`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			  AND `deleted` = 0
			ORDER BY `page` ASC
		");
	}

	/* STEP 5 */
	/* **************************************************************************** */
	function db_setTidyCode($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `tidy` = {$in['tidy']}
			WHERE `id` = {$in['id']}
			LIMIT 1
		");
	}

	/* STEP 6 */
	/* **************************************************************************** */
	// Also used in Step 7
	function db_setCleanCode($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `clean` = {$in['clean']}
			WHERE `id` = {$in['id']}
			LIMIT 1
		");
	}

	/* STEP 7 */
	/* **************************************************************************** */
	function db_setPageSimple($in) { cleanup($in);
		return db_MAIN("
			INSERT INTO `migrate_content`
				(`title`, `site`, `page`, `html`, `crawled`, `page_slug`)
			VALUES(
				{$in['title']},
				{$in['site']},
				'-',
				'<!-- Empty page, created in Migrate 2 WordPress -->',
				0,
				{$in['slug']}
			)
		");
	}

	function db_getParentPagesFromSite($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `title`, `page`, `crawled`, `page_slug`, `page_parent`, `page_sort`, `deleted`
			FROM `migrate_content`
			WHERE `site` = {$in['site']}
			AND `page_parent` = 0
			ORDER BY
				CASE 
				WHEN `page_parent` = 0
				THEN
					CASE WHEN `page_sort` = 0 THEN `id` ELSE `page_sort` END
				ELSE `page_parent`
				END
			ASC, `id` ASC
		");
	}

	/* STEP 8 */
	/* **************************************************************************** */
	function db_updateContentLinks($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `ready` = REPLACE(`clean`, {$in['oldlink']}, {$in['newlink']})
			WHERE `site` = {$in['site']}
		");
	}
	// Also used in export step
	function db_getContentDataFromSite($in) { cleanup($in);
		return db_MAIN("
			SELECT c.`id`, c.`page`, c.`title`, c.`crawled`, c.`content`, c.`wash`, c.`tidy`, c.`clean`, c.`ready`, c.`page_parent`, c.`page_slug`, cc.`page_slug` AS `parent_slug`
			FROM `migrate_content` c
			LEFT OUTER JOIN `migrate_content` cc
			ON c.`page_parent` = cc.`id`
			WHERE c.`site` = {$in['site']}
			  AND c.`deleted` = 0
			ORDER BY c.`id` ASC
		");
	}
	function db_setReadyCode($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_content`
			SET `ready` = {$in['ready']}
			WHERE `id` = {$in['id']}
			LIMIT 1
		");
	}


	//////////////////////////////////////////////////////////////////////////////////
	// PROJECTS
	//////////////////////////////////////////////////////////////////////////////////

	function db_getSites() {
		return db_MAIN("
			SELECT s.`id`, s.`name`, s.`url`, s.`new_url`, s.`step`, COUNT(c.`id`) AS `pages`
			FROM `migrate_sites` s
			LEFT OUTER JOIN `migrate_content` c
			ON c.`site` = s.`id`
			GROUP BY s.`id`
			ORDER BY s.`id` DESC
		");
	}
	function db_getSite($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `name`, `url`, `new_url`, `step`
			FROM `migrate_sites`
			WHERE id = {$in['id']}
		");
	}
	function db_setSite($in) { cleanup($in);
		return db_MAIN("
			INSERT INTO `migrate_sites`
				(`name`,`url`,`new_url`)
			VALUES(
				{$in['name']},
				{$in['url']},
				{$in['new_url']}
			)
		");
	}
	function db_setUpdateSite($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_sites`
			SET
				`name` = {$in['name']},
				`url` = {$in['url']},
				`new_url` = {$in['new_url']}
			WHERE `id` = {$in['id']}
		");
	}
	function db_delSite($in) { cleanup($in);
		return db_MAIN("
			DELETE FROM `migrate_sites`
			WHERE `id` = {$in['id']}
		");
	}
	function db_delSiteContent($in) { cleanup($in);
		return db_MAIN("
			DELETE FROM `migrate_content`
			WHERE `site` = {$in['site']}
		");
	}

	//////////////////////////////////////////////////////////////////////////////////
	// USERS
	//////////////////////////////////////////////////////////////////////////////////

	function db2_getUserLoginInfo($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `name`, `username`, `password`, `mail`, `level`
			FROM `migrate_users`
			WHERE `mail` LIKE {$in['mail']}
			LIMIT 1
		;");
	}
	function db2_getUsers() {
		return db_MAIN("
			SELECT `id`, `name`, `username`, `password`, `mail`, `level`
			FROM `migrate_users`
			ORDER BY `id` DESC
		");
	}
	function db2_getUser($in) { cleanup($in);
		return db_MAIN("
			SELECT `id`, `name`, `username`, `password`, `mail`, `level`
			FROM `migrate_users`
			WHERE id = {$in['id']}
		");
	}
	function db2_setUpdateUser($in) { cleanup($in);
		return db_MAIN("
			UPDATE `migrate_users`
			SET
				`name` = {$in['name']},
				`username` = {$in['username']},
				`mail` = {$in['mail']},
				`password` = {$in['password']},
				`level` = {$in['level']}
			WHERE `id` = {$in['id']}
		");
	}
	function db2_setUser($in) { cleanup($in);
		return db_MAIN("
			INSERT INTO `migrate_users`
				(`name`,`username`,`mail`,`password`,`level`)
			VALUES(
				{$in['name']},
				{$in['username']},
				{$in['mail']},
				{$in['password']},
				{$in['level']}
			)
		");
	}
	function db2_delUser($in) { cleanup($in);
		return db_MAIN("
			DELETE FROM `migrate_users`
			WHERE `id` = {$in['id']}
		");
	}

?>
