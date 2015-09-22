<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-manage-search.php';
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dsearch_replace
 * @subpackage Dsearch_replace/admin
 * @author     Abhishek Gupta <abhishek.gupta@daffodilsw.com>
 */
class Dsearch_replace_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The options name to be used in this plugin
     *
     * @since  	1.0.0
     * @access 	private
     * @var  	string 		$option_name 	Option name of this plugin
     */
    private $option_name = 'search_replace';
    
    /**
     * The response of plugin.
     *
     * @since  	1.0.0
     * @access 	private
     * @var  	string $response
     */
    private $response;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * An instance of this class should be passed to the run() function
         * defined in Dsearch_replace_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Dsearch_replace_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/dsearch_replace-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * An instance of this class should be passed to the run() function
         * defined in Dsearch_replace_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Dsearch_replace_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/dsearch_replace-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Add an setting page under main menu
     *
     * @since  1.0.0
     */
    public function add_options_page() {
        $this->plugin_screen_hook_suffix = add_menu_page(
                __('Search Replace Settings', 'search-replace'),
                __('Search Replace', 'serach-replace'), 
                'manage_options', $this->plugin_name, 
                array($this, 'display_options_page')
        );
    }

    /**
     * Render the setting page for plugin
     *
     * @since  1.0.0
     */
    public function display_options_page() {
        if(isset($_POST['submit'])){
         $response = $this->db_search_action($_POST);
         $this->response = $response;
        }
        include_once 'partials/dsearch_replace-admin-display.php';
    }

    /**
     * Register the setting page for plugin
     *
     * @since  1.0.0
     */
    public function register_setting() {
        // Add a General section
        add_settings_section(
                $this->option_name . '_general', __('General', 'serach_replace'), array($this, $this->option_name . '_general_cb'), $this->plugin_name
        );
        add_settings_field(
                $this->option_name . '_manage', __('Search And Replace', 'serach_replace'), array($this, $this->option_name . '_manage_cb'), $this->plugin_name, $this->option_name . '_general'
        );
    }

    /**
     * Render the text for the general section
     *
     * @since  1.0.0
     */
    public function search_replace_general_cb() {
        echo '<p>' . __('Please enter appropriate values.', 'outdated-serach_replace') . '</p>';
    }


    /**
     * Render the view for plugin
     *
     * @since  1.0.0
     */
    public function search_replace_manage_cb() {
        echo "<br />";
        echo  __('Search string', 'search-notice').'<input type="text" name="' . $this->option_name . '_search' . '" id="' . $this->option_name . '_search' . '"> ';
        echo __('Replace string', 'replace-notice').'<input type="text" name="' . $this->option_name . '_replace' . '" id="' . $this->option_name . '_replace' . '"> ' ;
        $this->getTablesHtml();
    }
    
    /**
     * Get html for table select box
     * 
     * @since  1.0.0
     * @return string
     */
    public function getTablesHtml(){
        $obj = new Manage_search();
        $tables = $obj->get_tables();
        echo '<select name="tables"> ';
        foreach($tables as $table){
            echo "<option value=$table>$table</option>";
        }
        echo '</select>';
    }
            


    /**
     * Handle request then generate response using echo or leaving PHP and using HTML
     * 
     * @since  1.0.0
     * @param array $data
     * @return string
     */
    function db_search_action($data) {
        $obj = new Manage_search();
        $result = $obj->manageSearch($data);
        return json_encode($result);
    }

}
