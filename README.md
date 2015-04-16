Migrate 2 WordPress, 2.0 - BETA
================

Migrate your static files to Wordpress
------------------

*This repo is loosely based on "[Bobby CMS](https://github.com/Bellfalasch/Bobby-CMS)", which is in development (basically just a simple CRUD-system to generate forms for the database).*

The main function of Migrate 2 WordPress is to first crawl/scrape an old site (perhaps built with static html-files), then clean up / tidy the html code, and end it all by handing you a downloadable XML-file that can be imported straight into Wordpress.

This project is in Alpha/Beta stage until we'll reach the "Beta 1.0"-version. Check the CHANGELOG.md for more details. It's highly recommended to use proper backups of your database before using this code, and expect big changes between each beta release, and no upgrade models.


Disclaimer
----------------

This project is not made to be working universally on every setup there is. I made it to assist myself in porting some old code from about 10 different sites. Don't expect it to automagically work on every kind of weird old setup. It's only tested to work on WordPress 3.6 to 4.1 and doesn't take into account that WordPress export format might change in the future.

It won't handle URL's based on folders, as it expects a file ending to validate a file as crawlable. It doesn't handle JavaScript (or Ajax) at all, or Flash.

Also, don't expect it to produce perfect result from old code. It will do the best it can. You won't get away from having to manually editing some pages in the end anyway, but the amount of work is greatly reduced.

Migrate 2 WordPress doesn't create any pages inside WordPress, that is up to you! And it does not create Menus, Images, or other fancy things. It's just for crawling, formatting, and exporting.

It won't support content spread into many different "blocks" / areas on a single page. It only supports one starting point, and then one ending point. Everything between them will be counted as content.

It's based on a somewhat slow fopen-crawl, this might be changed in the future. We had no clue about Curl when we started the project.


Installation:
----------------

Look at the list of dependencies after this section. Make sure all is set up. Open phpMyAdmin (or similiar), create a database called "test" and execute everything in the included file "/DATABASE.sql". Now upload all the files to your server (or localhost). It *should* work in any folder structure.

Log in with "admin@example.com" and "password" in the login form (you should be redirected automatically when you open the projects root folder).


Dependencies:
----------------

This admin is based and tested on: 

### PHP
* Version 4.3.10
* Settings: short open tags = true
* Settings: allow url fopen = true
* Extensions: php_mysqli = ON
* Extensions: php_tidy = ON

### MySQL
* Version 5.5.20
* InnoDB used as engine

### Bobby CMS
* Version 0.9.2.1
* Developed by me =)
* https://github.com/Bellfalasch/Bobby-CMS
* Used as included files

Bobby CMS uses Bootstrap 2.3.1 (because I started the project when that was the latest and greatest, and not changed because I don't like the new flat style of Boostrap 3), TinyMCE, and jQuery 1.8.1. All included. Update/remove at own risk!


Basic file structure and form-generation:
----------------

Check out the readme for [Bobby CMS](https://github.com/Bellfalasch/Bobby-CMS) if you need more information on basic functions, structure, etc that this project uses. All forms are built using that project too. It has a way of setting up forms easily by defining arrays of options. Some code and boom - form for editing, adding, and validation is generated. It's far from finished, but at least it works good enough to be used here =)
