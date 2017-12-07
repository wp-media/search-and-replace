# Changelog

## 3.2.0 (ToDo)
* Added CSV format alternative for search/replace [#82].
* Change code structure, preparation for more solid UnitTests.
* Improve Modal Table UI.

## v3.1.2 (2016-12-31)
- hotfix: prevent declaration error with Requisite

## v3.1.1 (2016-12-24)
- Refactor Plugin loading [#67](https://github.com/inpsyde/search-and-replace/issues/67)
- Display error notice is the search value the current domain and save changes to Database selected

## v3.1.0-RC1 (2016-04-07)
- Improve codquality
- Prepared for localization (GlotPress)
- Prevent doing idle prozesses if search & replace pattern the same
- Implement better BigData handling.
- Add support for Searchpattern with quotes. [#40](https://github.com/inpsyde/search-and-replace/issues/40)
- Basic travis support for travis was added. [#38](https://github.com/inpsyde/search-and-replace/issues/38)
- Fix Unittest [#37](https://github.com/inpsyde/search-and-replace/issues/37)


## v3.0.2 (2016-04-01)
- Implement better tab and adminpage handling [#33](https://github.com/inpsyde/search-and-replace/issues/33)
- Prepare the Plugin for localization, change Text-Domain.[#47](https://github.com/inpsyde/search-and-replace/issues/47)
- Remove difference in wordings for buttons between descriptions.[#46](https://github.com/inpsyde/search-and-replace/issues/46)

## v3.0.0 (2016-01-27)
- Refactor the plugin, new requirements, goal and result.
- -Thanks to [Sven Hinse](https://github.com/s-hinse/) for help to maintain the plugin-
- Changeable table prefix on replace Site URL tab enhancement
- Implement database backup & import tab
- Implement Dry Run: Keep for Search and Replace
- Prevent self destroy
- Multisite basic support - Show only tables of current site
- Add spectial tab for replace the url
- Supports serialized data
- Refactor the whole codebase

## v2.7.1 (2015-05-28)
- Fix for changes on database collate since WordPress version 4.2
- Fix to reduce backslashes in search and replace string

## v2.7.0 (2014-09-14)
- Exclude serialized data from replace function (maybe we reduce the support)
- Add hint, if is serialized data on the result table
- Fix to see also the result case sensitive

## v2.6.6 (09/05/2014)
- -Thanks to [Ron Guerin](http://wordpress.org/support/profile/rong) for help to maintain the plugin-
- Fix to use $wpdb object for all database access
- Fix inability to search and replace quoted strings
- Output changes to clarify when searching vs. searching and replacing
- Some changes to English strings and string identifiers

## v2.6.5
- Fix for change User-ID, add table `comments`

## v2.6.4
- Fix capability check, if the constant `DISALLOW_FILE_EDIT` ist defined

## v2.6.3 (10/10/2011)
- filter for return values, html-filter
- add belarussian language
- add romanian language files

## v2.6.2 (09/11/2011)
- change right object for use the plugin also on WP smaller 3.0, include 2.9
- add function search and replace in all tables of the database - special care!

## v2.6.1 (01/25/2011)
- Feature: Add Signups-Table for WP MU
- Maintenance: check for tables, PHP Warning fix

## v2.6.0 (01/03/2011)
- Feature: add an new search for find strings (maybe a new way for search strings)
- Maintenance: small changes on source

## v2.5.1 (07/07/2010)
- small changes for use in WP 3.0