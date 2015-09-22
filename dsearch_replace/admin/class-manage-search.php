<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/wpmdb-replace.php';

/**
 * Manage search and replace
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Dsearch_replace
 * @subpackage Dsearch_replace/includes
 * @author     Abhishek Gupta <abhishek.gupta@daffodilsw.com>
 */
class Manage_search {

    public $name = '';
    public $report_change_num = 30;
    public $page_size = 10;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->name = $wpdb->dbname; //get database name
    }

    /**
     * Define the manage search
     *
     * @since    1.0.0
     * @param array $data.
     */
    public function manageSearch($data) {
        $search_string = $data['search_replace_search'];
        $replace_string = $data['search_replace_replace'];
        $table = array($data['tables']); /*@TODO get array of tables from form */
        $result = $this->replacer($search_string, $replace_string, $table);
        return $result;
    }

    /**
     * This walks every table in the db that was selected and then
     * walks every row and column replacing all occurences of a string with another.
     *
     * @param string $search     What we want to replace
     * @param string $replace    What we want to replace it with.
     * @param array  $tables     The tables we want to look at.
     *
     * @return array    Collection of information gathered during the run.
     */
    public function replacer($search = '', $replace = '', $tables = array()) {
        global $wpdb;
        $time = microtime();
        $report = array(
            'tables' => 0,
            'rows' => 0,
            'change' => 0,
            'updates' => 0,
            'start' => $time,
            'end' => $time,
            'errors' => array(),
            'table_reports' => array()
        );

        $table_report = array(
            'rows' => 0,
            'change' => 0,
            'changes' => array(),
            'updates' => 0,
            'start' => $time,
            'end' => $time,
            'errors' => array(),
        );

        //$tables = array('wp_posts'); /* @TODO  Need to get table name from form*/
        // if no tables selected assume all
        if (empty($tables)) {
            $all_tables = $this->get_tables();
            $tables = $all_tables;
        }

        if (is_array($tables) && !empty($tables)) {
            foreach ($tables as $key => $table) {
                $encoding = $this->get_table_character_set($table);
                switch ($encoding) {
                    // Tables encoded with this work for me only when I set names to utf8. I don't trust this in the wild so I'm going to avoid.
                    case 'utf16':
                    case 'utf32':
                        $encoding = 'utf8';
                        $this->log_error("The table \"{$table}\" is encoded using \"{$encoding}\" which is currently unsupported.");
                        continue;
                        break;

                    default:
                        // @TODO need to handle this
                        break;
                }


                $report['tables'] ++;

                // get primary key and columns
                list( $primary_key, $columns ) = $this->get_columns($table);

                if ($primary_key === null) {
                    $this->log_error("The table \"{$table}\" has no primary key. Changes will have to be made manually.");
                    continue;
                }

                // create new table report instance
                $new_table_report = $table_report;
                $new_table_report['start'] = microtime();

                // Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
                $row_count = $this->getRowCount($table);

                $page_size = $this->page_size;
                $pages = ceil($row_count / $page_size);

                for ($page = 0; $page < $pages; $page++) {

                    $start = $page * $page_size;
                    // Grab the content of the table
                    $data = $wpdb->get_results(sprintf('SELECT * FROM `%s` LIMIT %d, %d', $table, $start, $page_size), ARRAY_A);

                    if (!$data)
                        $this->log_error('No record found');
                    foreach ($data as $row) {
                        $report['rows'] ++; // Increment the row counter
                        $new_table_report['rows'] ++;

                        $update_sql = array();
                        $where_sql = array();
                        $update = false;

                        foreach ($columns as $column) {

                            $edited_data = $data_to_fix = $row[$column];

                            if ($primary_key == $column) {
                                $where_sql[] = "`{$column}` = " . $data_to_fix;
                                continue;
                            }

                            // Run a search replace on the data that'll respect the serialisation.
                            $args = array(
                                'table' => $table,
                                'search' => $search,
                                'replace' => $replace,
                                'intent' => '',
                                'base_domain' => '',
                                'site_domain' => '',
                                'wpmdb' => $this
                            );
                            $wpmdb = new WPMDB_Replace($args); //get WPMDB_Replace class object
                            $edited_data = $wpmdb->recursive_unserialize_replace($data_to_fix);
                            // Something was changed
                            if ($edited_data != $data_to_fix) {

                                $report['change'] ++;
                                $new_table_report['change'] ++;

                                if ($new_table_report['change'] <= $this->report_change_num) {
                                    $new_table_report['changes'][] = array(
                                        'row' => $new_table_report['rows'],
                                        'column' => $column,
                                        'from' => utf8_encode($data_to_fix),
                                        'to' => utf8_encode($edited_data)
                                    );
                                }

                                $update_sql[] = "`{$column}` = " . "'" . $edited_data . "'";
                                $update = true;
                            }
                        }

                        if ($update && !empty($where_sql)) {
                            $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $update_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
                            $result = $this->db_update($sql);
                            if (!is_int($result) && !$result) {
                                $this->log_error('No record found');
                            } else {
                                $report['updates'] ++;
                                $new_table_report['updates'] ++;
                            }
                        }
                    }
                    $wpdb->flush();
                }

                $new_table_report['end'] = microtime();

                // store table report in main
                $report['table_reports'][$table] = $new_table_report;

                // log result
                $this->log_error('search_replace_table_end for table: ' . $table . " Table Report :" . $new_table_report);
            }
        }

        $report['end'] = microtime();
        return $report;
    }

    /**
     * Retrieve all tables from the database
     * 
     * $since 1.0.0
     * @return array
     */
    public function get_tables() {
        global $wpdb;
        $all_tables = array();
        // get tables
        // A clone of show table status but with character set for the table.
        $show_table_status = "SELECT
		  t.`TABLE_NAME` as Name,
		  t.`ENGINE` as `Engine`,
		  t.`version` as `Version`,
		  t.`ROW_FORMAT` AS `Row_format`,
		  t.`TABLE_ROWS` AS `Rows`,
		  t.`AVG_ROW_LENGTH` AS `Avg_row_length`,
		  t.`DATA_LENGTH` AS `Data_length`,
		  t.`MAX_DATA_LENGTH` AS `Max_data_length`,
		  t.`INDEX_LENGTH` AS `Index_length`,
		  t.`DATA_FREE` AS `Data_free`,
		  t.`AUTO_INCREMENT` as `Auto_increment`,
		  t.`CREATE_TIME` AS `Create_time`,
		  t.`UPDATE_TIME` AS `Update_time`,
		  t.`CHECK_TIME` AS `Check_time`,
		  t.`TABLE_COLLATION` as Collation,
		  c.`CHARACTER_SET_NAME` as Character_set,
		  t.`Checksum`,
		  t.`Create_options`,
		  t.`table_Comment` as `Comment`
		FROM information_schema.`TABLES` t
			LEFT JOIN information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` c
				ON ( t.`TABLE_COLLATION` = c.`COLLATION_NAME` )
		  WHERE t.`TABLE_SCHEMA` = '{$this->name}';
		";

        $all_tables_mysql = $wpdb->get_results($show_table_status);

        $all_tables = array();

        if (!$all_tables_mysql) {
            /* @TODO  handle error */
        } else {
            foreach ($all_tables_mysql as $single_tables_mysql) {
                $all_tables[] = $single_tables_mysql->Name;
            }
        }
        return $all_tables;
    }

    /**
     * Get the character set for the current table
     *
     * $since 1.0.0
     * @param string $table_name The name of the table we want to get the char
     * set for
     *
     * @return string    The character encoding;
     */
    public function get_table_character_set($table_name = '') {
        global $wpdb;
        $schema = $this->name;

        $charset = $wpdb->get_results("SELECT c.`character_set_name`
			FROM information_schema.`TABLES` t
				LEFT JOIN information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` c
				ON (t.`TABLE_COLLATION` = c.`COLLATION_NAME`)
			WHERE t.table_schema = {$schema}
				AND t.table_name = {$table_name}
			LIMIT 1;");

        $encoding = false;
        if (!$charset) {
            $this->log_error( 'No record found' );
        } else {
            $result = $charset;
            $encoding = isset($result['character_set_name']) ? $result['character_set_name'] : false;
        }
        return $encoding;
    }

    /**
     * Get Columns
     * 
     * $since 1.0.0
     * @global type $wpdb
     * @param type $table
     * @return type
     */
    public function get_columns($table) {
        global $wpdb;
        $primary_key = null;
        $columns = array();
        // Get a list of columns in this table
        $fields = $wpdb->get_results("DESCRIBE {$table}");
        if (!$fields) {
            //$this->add_error( $this->db_error( ), 'db' );
        } else {
            foreach ($fields as $column) {
                $columns[] = $column->Field;
                if ($column->Key == 'PRI')
                    $primary_key = $column->Field;
            }
        }
        return array($primary_key, $columns);
    }

    /**
     * Get row count
     * 
     * $since 1.0.0
     * @global type $wpdb
     * @param type $table
     * @return int
     */
    public function getRowCount($table) {
        global $wpdb;
        $query = "SELECT COUNT(*) as count FROM {$table}";
        $data = $wpdb->get_results($query);
        $row_count = $data[0]->count;
        return $row_count;
    }

    /**
     * Check for valid json type
     * 
     * $since 1.0.0
     * @param string $string
     * @param boolean $status
     * @return boolean
     */
    public function is_json($string, $status) {
      // json_decode($string, $status);
      // return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Maintain log
     * $since 1.0.0
     */
    public function log_error($string) {
        $file = plugin_dir_path(dirname(__FILE__)) . 'logs/error.log';
        //chmod($file, 0777);
        //error_log($string, 3, $file);
    }

    /**
     * Update table
     * 
     * $since 1.0.0
     * @param string $sql
     * @return boolean
     */
    public function db_update($sql) {
        global $wpdb;
        $result = $wpdb->query($sql);
        return $result;
    }

}
