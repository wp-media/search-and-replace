<?php
/**
 * Test case.
 *
 * This is used to define test case
 *
 * @since      1.0.0
 * @author     Abhishek Gupta <abhishek.gupta@daffodilsw.com>
 */
class test_search  extends WP_UnitTestCase {
    
    /**
     * Check if plugin has initialised.
     */
     function testPluginInitialization() {
        $plugin = new Dsearch_replace();
        $plugin_name = $plugin->get_plugin_name();
        $this->assertFalse(null == $plugin_name);
   }
    
}
