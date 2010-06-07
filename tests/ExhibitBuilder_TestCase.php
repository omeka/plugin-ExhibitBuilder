<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 **/
class ExhibitBuilder_TestCase extends Omeka_Test_AppTestCase
{
    const PLUGIN_NAME = 'ExhibitBuilder';
    
    public function setUp()
    {
        parent::setUp();
        
        // Authenticate and set the current user 
        $this->user = $this->db->getTable('User')->find(1);
        $this->_authenticateUser($this->user);
        Omeka_Context::getInstance()->setCurrentUser($this->user);
                
        // Add the plugin hooks and filters (including the install hook)
        $pluginBroker = get_plugin_broker();
        $this->_addPluginHooksAndFilters($pluginBroker, self::PLUGIN_NAME);
        
        // Install the plugin
        $plugin = $this->_installPlugin(self::PLUGIN_NAME);
        $this->assertTrue($plugin->isInstalled());
        
        // Initialize the core resource plugin hooks and filters (like the initialize hook)
        $this->_initializeCoreResourcePluginHooksAndFilters($pluginBroker, self::PLUGIN_NAME);
    }
        
    public function _addPluginHooksAndFilters($pluginBroker, $pluginName)
    {   
        // Set the current plugin so the add_plugin_hook function works
        $pluginBroker->setCurrentPluginDirName($pluginName);
        
        // Add plugin hooks
        add_plugin_hook('install', 'exhibit_builder_install');
        add_plugin_hook('uninstall', 'exhibit_builder_uninstall');
        add_plugin_hook('define_acl', 'exhibit_builder_setup_acl');
        add_plugin_hook('define_routes', 'exhibit_builder_routes');
        add_plugin_hook('public_theme_header', 'exhibit_builder_public_header');
        add_plugin_hook('admin_theme_header', 'exhibit_builder_admin_header');
        add_plugin_hook('admin_append_to_dashboard_primary', 'exhibit_builder_dashboard');
        add_plugin_hook('html_purifier_form_submission', 'exhibit_builder_purify_html');
        
        // Add plugin filters
        add_filter('public_navigation_main', 'exhibit_builder_public_main_nav');
        add_filter('admin_navigation_main', 'exhibit_builder_admin_nav');     
    }

}