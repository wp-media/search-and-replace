=== Search and Replace ===
Contributors: Bueltge, inpsyde
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RHWH8VG798CSC
Tags: database, mysql, search, replace, admin, security
Requires at least: 3.0
Tested up to: 4.2.2
Stable tag: 2.7.1
License: GPLv2+

A simple search for find strings in your database and replace the string.

== Description ==
A simple search for find strings in your database and replace the string. You can search in ID, post-content, GUID, titel, excerpt, meta-data, comments, comment-author, comment-e-mail, comment-url, tags/categories and categories-description. It is possible to replace the user-ID in all tables and the user-login.

"Search and Replace" Originalplugin ist von [Mark Cunningham](http://thedeadone.net/ "Mark Cunningham") and was advanced (comments and comment-author) by [Gonahkar](http://www.gonahkar.com/ "Gonahkar"). Much more enhanced and maintained by [Frank Bültge](http://bueltge.de) and current version is also maintained by Ron Guerin <ron@vnetworx.net>.

== Installation ==
= Requirements =
* WordPress version 3.0 and later (tested at 3.5-Beta2 and 3.3.2)

= Installation =
1. Unpack the download-package
1. Upload search-and-replace folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Got to Tools -> Search/Replace

== Screenshots ==
1. Functions in WordPress 2.7 beta
1. The search for an string in WordPress 3.1-RC2

== Other Notes ==
= Acknowledgements =
All existing translation files need to be updated for Search and Replace 2.6.6, please feel free to send me your translation file.

= License =
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

= Translations =
The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the .pot file which contains all definitions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows) or plugin for WordPress [Localization](http://wordpress.org/extend/plugins/codestyling-localization/).


== Changelog ==
= v2.7.1 (2015-05-28) =
* Fix for changes on database collate since WordPress version 4.2
* Fix to reduce backslashes in search and replace string

= v2.7.0 (2014-09-14) =
* Exclude serialized data from replace function (maybe we reduce the support)
* Add hint, if is serialized data on the result table
* Fix to see also the result case sensitive

= v2.6.6 (09/05/2014) =
* *Thanks to [Ron Guerin](http://wordpress.org/support/profile/rong) for help to maintain the plugin*
* Fix to use $wpdb object for all database access
* Fix inability to search and replace quoted strings
* Output changes to clarify when searching vs. searching and replacing
* Some changes to English strings and string identifiers

= v2.6.5 =
* Fix for change User-ID, add table `comments`

= v2.6.4 =
* Fix capability check, if the constant `DISALLOW_FILE_EDIT` ist defined

= v2.6.3 (10/10/2011) =
* filter for return values, html-filter
* add belarussian language
* add romanian language files

= v2.6.2 (09/11/2011) =
* change right object for use the plugin also on WP smaller 3.0, include 2.9
* add function search and replace in all tables of the database - special care!

= v2.6.1 (01/25/2011) =
* Feature: Add Signups-Table for WP MU
* Maintenance: check for tables, PHP Warning fix

= v2.6.0 (01/03/2011) =
* Feature: add an new search for find strings (maybe a new way for search strings)
* Maintenance: small changes on source

= v2.5.1 (07/07/2010) =
* small changes for use in WP 3.0
