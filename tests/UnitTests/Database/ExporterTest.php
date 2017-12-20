<?php

namespace Inpsyde\SearchReplace\Tests\Database;

use Inpsyde\SearchReplace\Database\Exporter;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;

class ExporterTest extends AbstractTestCase {

	public function test_search_and_replace() {

		\Brain\Monkey\Functions\when( 'get_temp_dir' )
			->justReturn( './' );
		\Brain\Monkey\Functions\when( 'esc_attr__' )
			->returnArg( 1 );
		\Brain\Monkey\Functions\when( '__' )
			->returnArg( 1 );

		$replace_mock  = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Replace' );
		$dbm_mock      = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Manager' );
		$wp_error_mock = \Mockery::mock( '\WP_Error' );

		$dbm_mock->shouldReceive( 'get_base_prefix' )
			->andReturn( 'wp_' );
		$dbm_mock->shouldReceive( 'get_table_structure' )
			->andReturn(
				[
					(object) [
						'Field'   => 'post_title',
						'Type'    => 'text',
						'Null'    => 'NO',
						'Key'     => '',
						'Default' => '',
						'Extra'   => '',
					],
				]
			);
		$dbm_mock->shouldReceive( 'get_create_table_statement' )
			->andReturn(
				[
					0 => 'wp_posts',
					1 => 'CREATE TABLE `wp_3_posts` (
						  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						  `post_author` bigint(20) unsigned NOT NULL DEFAULT \'0\',
						  `post_date` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_date_gmt` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'publish\',
						  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'open\',
						  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'open\',
						  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_modified` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_modified_gmt` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_parent` bigint(20) unsigned NOT NULL DEFAULT \'0\',
						  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `menu_order` int(11) NOT NULL DEFAULT \'0\',
						  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'post\',
						  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `comment_count` bigint(20) NOT NULL DEFAULT \'0\',
						  PRIMARY KEY (`ID`),
						  KEY `post_name` (`post_name`(191)),
						  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
						  KEY `post_parent` (`post_parent`),
						  KEY `post_author` (`post_author`)
						) ENGINE=InnoDB AUTO_INCREMENT=1693 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',
				]
			);
		$dbm_mock->shouldReceive( 'get_columns' )
			->andReturn(
				[
					0 => 'ID',
					1 => [
						0  => "ID",
						1  => "post_author",
						2  => "post_date",
						3  => "post_date_gmt",
						4  => "post_content",
						5  => "post_title",
						6  => "post_excerpt",
						7  => "post_status",
						8  => "comment_status",
						9  => "ping_status",
						10 => "post_password",
						11 => "post_name",
						12 => "to_ping",
						13 => "pinged",
						14 => "post_modified",
						15 => "post_modified_gmt",
						16 => "post_content_filtered",
						17 => "post_parent",
						18 => "guid",
						19 => "menu_order",
						20 => "post_type",
						21 => "post_mime_type",
						22 => "comment_count",
					],
				]
			);
		$dbm_mock->shouldReceive( 'get_rows' )
			->andReturn( 1 );
		$dbm_mock->shouldReceive( 'get_table_content' )
			->andReturn(
				[
					[
						"ID"                    => '',
						"post_author"           => '',
						"post_date"             => '',
						"post_date_gmt"         => '',
						"post_content"          => '',
						"post_title"            => 'search',
						"post_excerpt"          => '',
						"post_status"           => '',
						"comment_status"        => '',
						"ping_status"           => '',
						"post_password"         => '',
						"post_name"             => '',
						"to_ping"               => '',
						"pinged"                => '',
						"post_modified"         => '',
						"post_modified_gmt"     => '',
						"post_content_filtered" => '',
						"post_parent"           => '',
						"guid"                  => '',
						"menu_order"            => '',
						"post_type"             => '',
						"post_mime_type"        => '',
						"comment_count"         => '',
					],
				]
			);

		$wp_error_mock->shouldReceive( 'add' )
			->zeroOrMoreTimes();

		$instance = new Exporter( $replace_mock, $dbm_mock, $wp_error_mock );
		$response = $instance->backup_table( 'search', 'replace', 'wp_simple_table' );

		$this->assertSame( 1, $response[ 'change' ] );
	}

	public function test_guid_skip() {

		\Brain\Monkey\Functions\when( 'get_temp_dir' )
			->justReturn( './' );
		\Brain\Monkey\Functions\when( 'esc_attr__' )
			->returnArg( 1 );
		\Brain\Monkey\Functions\when( '__' )
			->returnArg( 1 );

		$replace_mock  = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Replace' );
		$dbm_mock      = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Manager' );
		$wp_error_mock = \Mockery::mock( '\WP_Error' );

		$dbm_mock->shouldReceive( 'get_base_prefix' )
			->andReturn( 'wp_' );
		$dbm_mock->shouldReceive( 'get_table_structure' )
			->andReturn(
				[
					(object) [
						'Field'   => 'guid',
						'Type'    => 'varchar(255)',
						'Null'    => 'NO',
						'Key'     => '',
						'Default' => '',
						'Extra'   => '',
					],
				]
			);
		$dbm_mock->shouldReceive( 'get_create_table_statement' )
			->andReturn(
				[
					0 => 'wp_posts',
					1 => 'CREATE TABLE `wp_3_posts` (
						  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						  `post_author` bigint(20) unsigned NOT NULL DEFAULT \'0\',
						  `post_date` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_date_gmt` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'publish\',
						  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'open\',
						  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'open\',
						  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_modified` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_modified_gmt` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_parent` bigint(20) unsigned NOT NULL DEFAULT \'0\',
						  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `menu_order` int(11) NOT NULL DEFAULT \'0\',
						  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'post\',
						  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `comment_count` bigint(20) NOT NULL DEFAULT \'0\',
						  PRIMARY KEY (`ID`),
						  KEY `post_name` (`post_name`(191)),
						  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
						  KEY `post_parent` (`post_parent`),
						  KEY `post_author` (`post_author`)
						) ENGINE=InnoDB AUTO_INCREMENT=1693 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',
				]
			);
		$dbm_mock->shouldReceive( 'get_columns' )
			->andReturn(
				[
					0 => 'ID',
					1 => [
						0  => "ID",
						1  => "post_author",
						2  => "post_date",
						3  => "post_date_gmt",
						4  => "post_content",
						5  => "post_title",
						6  => "post_excerpt",
						7  => "post_status",
						8  => "comment_status",
						9  => "ping_status",
						10 => "post_password",
						11 => "post_name",
						12 => "to_ping",
						13 => "pinged",
						14 => "post_modified",
						15 => "post_modified_gmt",
						16 => "post_content_filtered",
						17 => "post_parent",
						18 => "guid",
						19 => "menu_order",
						20 => "post_type",
						21 => "post_mime_type",
						22 => "comment_count",
					],
				]
			);
		$dbm_mock->shouldReceive( 'get_rows' )
			->andReturn( 1 );
		$dbm_mock->shouldReceive( 'get_table_content' )
			->andReturn(
				[
					[
						"ID"                    => '',
						"post_author"           => '',
						"post_date"             => '',
						"post_date_gmt"         => '',
						"post_content"          => '',
						"post_title"            => '',
						"post_excerpt"          => '',
						"post_status"           => '',
						"comment_status"        => '',
						"ping_status"           => '',
						"post_password"         => '',
						"post_name"             => '',
						"to_ping"               => '',
						"pinged"                => '',
						"post_modified"         => '',
						"post_modified_gmt"     => '',
						"post_content_filtered" => '',
						"post_parent"           => '',
						"guid"                  => 'http://www.search.com/',
						"menu_order"            => '',
						"post_type"             => '',
						"post_mime_type"        => '',
						"comment_count"         => '',
					],
				]
			);

		$wp_error_mock->shouldReceive( 'add' )
			->zeroOrMoreTimes();

		$instance = new Exporter( $replace_mock, $dbm_mock, $wp_error_mock );
		$response = $instance->backup_table( 'search', 'replace', 'wp_simple_table' );

		$this->assertSame( 0, $response[ 'change' ] );
	}

	public function test_new_db_prefix() {

		\Brain\Monkey\Functions\when( 'get_temp_dir' )
			->justReturn( './' );
		\Brain\Monkey\Functions\when( 'esc_attr__' )
			->returnArg( 1 );
		\Brain\Monkey\Functions\when( '__' )
			->returnArg( 1 );
		\Brain\Monkey\Functions\when('is_serialized')
			->alias([$this, 'is_serialized']);

		$replace_mock  = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Replace' );
		$dbm_mock      = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Manager' );
		$wp_error_mock = \Mockery::mock( '\WP_Error' );

		$dbm_mock->shouldReceive( 'get_base_prefix' )
			->andReturn( 'wp_' );
		$dbm_mock->shouldReceive( 'get_table_structure' )
			->andReturn(
				[
					(object) [
						'Field'   => 'guid',
						'Type'    => 'varchar(255)',
						'Null'    => 'NO',
						'Key'     => '',
						'Default' => '',
						'Extra'   => '',
					],
				]
			);
		$dbm_mock->shouldReceive( 'get_create_table_statement' )
			->andReturn(
				[
					0 => 'wp_posts',
					1 => 'CREATE TABLE `wp_3_posts` (
						  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						  `post_author` bigint(20) unsigned NOT NULL DEFAULT \'0\',
						  `post_date` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_date_gmt` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'publish\',
						  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'open\',
						  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'open\',
						  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_modified` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_modified_gmt` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
						  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
						  `post_parent` bigint(20) unsigned NOT NULL DEFAULT \'0\',
						  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `menu_order` int(11) NOT NULL DEFAULT \'0\',
						  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'post\',
						  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT \'\',
						  `comment_count` bigint(20) NOT NULL DEFAULT \'0\',
						  PRIMARY KEY (`ID`),
						  KEY `post_name` (`post_name`(191)),
						  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
						  KEY `post_parent` (`post_parent`),
						  KEY `post_author` (`post_author`)
						) ENGINE=InnoDB AUTO_INCREMENT=1693 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',
				]
			);
		$dbm_mock->shouldReceive( 'get_columns' )
			->andReturn(
				[
					0 => 'ID',
					1 => [
						0  => "ID",
						1  => "post_author",
						2  => "post_date",
						3  => "post_date_gmt",
						4  => "post_content",
						5  => "post_title",
						6  => "post_excerpt",
						7  => "post_status",
						8  => "comment_status",
						9  => "ping_status",
						10 => "post_password",
						11 => "post_name",
						12 => "to_ping",
						13 => "pinged",
						14 => "post_modified",
						15 => "post_modified_gmt",
						16 => "post_content_filtered",
						17 => "post_parent",
						18 => "guid",
						19 => "menu_order",
						20 => "post_type",
						21 => "post_mime_type",
						22 => "comment_count",
					],
				]
			);
		$dbm_mock->shouldReceive( 'get_rows' )
			->andReturn( 1 );
		$dbm_mock->shouldReceive( 'get_table_content' )
			->andReturn(
				[
					[
						"ID"                    => '',
						"post_author"           => '',
						"post_date"             => '',
						"post_date_gmt"         => '',
						"post_content"          => '',
						"post_title"            => '',
						"post_excerpt"          => '',
						"post_status"           => '',
						"comment_status"        => '',
						"ping_status"           => '',
						"post_password"         => '',
						"post_name"             => '',
						"to_ping"               => '',
						"pinged"                => '',
						"post_modified"         => '',
						"post_modified_gmt"     => '',
						"post_content_filtered" => '',
						"post_parent"           => '',
						"guid"                  => '',
						"menu_order"            => '',
						"post_type"             => '',
						"post_mime_type"        => '',
						"comment_count"         => '',
					],
				]
			);

		$wp_error_mock->shouldReceive( 'add' )
			->zeroOrMoreTimes();

		$instance = new Exporter( $replace_mock, $dbm_mock, $wp_error_mock );
		$response = $instance->backup_table( 'search', 'replace', 'wp_simple_table', 'new_table_prefix_' );

		$this->assertContains( 'new_table_prefix_', $response[ 'new_table_name' ] );
	}
}
