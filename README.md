# Search & Replace
[![Latest Stable Version](https://poser.pugx.org/inpsyde/search-and-replace/v/stable)](https://packagist.org/packages/inpsyde/search-and-replace) [![Latest Unstable Version](https://poser.pugx.org/inpsyde/search-and-replace/v/unstable)](https://packagist.org/packages/inpsyde/search-and-replace) [![License](https://poser.pugx.org/inpsyde/search-and-replace/license)](https://packagist.org/packages/inpsyde/search-and-replace) ![Travis Status](https://travis-ci.org/inpsyde/search-and-replace.svg)
[![Download stats on WordPress.org](http://www.rene-reimann.de/wporg-downloads-poser-badge.php?plugin-slug=search-and-replace)](https://wordpress.org/plugins/search-and-replace/stats/)



##Description:
- Backs up your database.
- Searches for strings in your WordPress database and replaces them, also in serialized objects.
- Generates a backup of database with changed site URL for site migration.
- Imports a SQL file into your database.

##Requirements:
PHP 5.4. +
WordPress 4.x (might work with 3.x, but not tested yet)

##Installation
1. Unpack the download package.
2. Upload the files to the /wp-content/plugins/ directory.
3. Activate the plugin in WordPress backend.

##Usage
Go to `Tools -> Search & Replace` to view the plugins user interface.

![screenshot-6](https://raw.githubusercontent.com/inpsyde/search-and-replace/master/assets/screenshot-6.png)

####*Backup Database* tab
**Urgent: Backup your database!**
Please **always** backup your database before performing search/replace operations.

**Multisite usage:**
If you are running a WordPress multisite and want to export the tables of all sites, run the plugin on the main site. Using the plugin on a subsite will only backup the tables of this subsite.

![screenshot-4](https://raw.githubusercontent.com/inpsyde/search-and-replace/master/assets/screenshot-4.png)

####*Replace Domain/URL* Tab
If you want to migrate your site to another domain, please enter here your new domain URL and download a sql file with replaced URLs. Import this file into the database of your WordPress installation on your new domain.

Please enter the new domain name including "http://" or "https://", as shown in the 'search' field.

If you want to replace the database-prefix, check the checkbox "replace database prefix" and enter your new prefix in the filed below. Don't forget to set the new prefix in the wp-config.php of your new installation!

Press *Replace Domain URL* button to download your SQL file for migration.

**Multisite usage:**
If you are running multisite and want to migrate the tables of all sites, run the plugin on the main site. Using the plugin on a subsite will only migrate the tables of this subsite. The plugin will also replace the domain names (without http(s)://) in the *wp_blogs* table.

![screenshot-2](https://raw.githubusercontent.com/inpsyde/search-and-replace/master/assets/screenshot-2.png)

####*Search and Replace* Tab
**Please backup your database before any search/replace!**
Enter your search/replace values and select the tables you want to search. Click on "Do search/replace" to start the operation.
By default, the checkbox "Dry run" is checked. In "dry run"- mode, you get a detailed preview of the changes. No changes will be written to the database. Please check always with "dry run" first.
If you want to apply the changes, uncheck the "dry run"-button, choose if you want to download a sql dump with the changes or write them into the database and click "do search/replace" again.
**Multisite usage:**
If you are running multisite and want to search the tables of all sites, run the plugin on the main site. Using the plugin on a subsite will only show the tables of this subsite.

![screenshot-1](https://raw.githubusercontent.com/inpsyde/search-and-replace/master/assets/screenshot-1.png)

####*Import SQL* tab
Import a SQL file or a gzipped SQL file into your database here. This may delete or change existing tables in your database. Please backup your database before doing this!

![screenshot-3](https://raw.githubusercontent.com/inpsyde/search-and-replace/master/assets/screenshot-3.png)

###License
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog.
