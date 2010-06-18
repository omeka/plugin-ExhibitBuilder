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
        add_plugin_hook('config_form', 'exhibit_builder_config_form');
        add_plugin_hook('config', 'exhibit_builder_config');
        add_plugin_hook('initialize', 'exhibit_builder_initialize');
        add_plugin_hook('html_purifier_form_submission', 'exhibit_builder_purify_html');
        
        // Add plugin filters
        add_filter('public_navigation_main', 'exhibit_builder_public_main_nav');
        add_filter('admin_navigation_main', 'exhibit_builder_admin_nav');     
    }
    
    protected function _createNewExhibit($isPublic, $isFeatured, $title, $description, $credits, $slug='')
	{
		$exhibit = new Exhibit;
		$exhibit->public = $isPublic ? 1 : 0;
        $exhibit->featured = $isFeatured ? 1 : 0;
		$exhibit->title = $title;
    	$exhibit->description = $description;
    	$exhibit->credits = $credits;
        
        if ($slug != '') {
            $exhibit->slug = $slug;
        }
    	
    	$exhibit->save();
    	
    	return $exhibit;
	}
	
	protected function _createNewExhibits($numberPublicNotFeatured = 5, $numberPublicFeatured = 5, $numberPrivateNotFeatured = 5, $numberPrivateFeatured = 5) 
	{
	    $exhibits = array();
        for ($i=0; $i < $numberPublicNotFeatured; $i++) {
            $exhibits[] = $this->_createNewExhibit(1, 0, 'Test Public Not Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'punf' . $i);
        }
        for ($i=0; $i < $numberPublicFeatured; $i++) {
            $exhibits[] = $this->_createNewExhibit(1, 1, 'Test Public Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'puf' . $i);   
        }
        for ($i=0; $i < $numberPrivateNotFeatured; $i++) {
            $exhibits[] = $this->_createNewExhibit(0, 0, 'Test Private Not Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'prnf' . $i);
        }
        for ($i=0; $i < $numberPrivateFeatured; $i++) {
            $exhibits[] = $this->_createNewExhibit(0, 1, 'Test Private Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'prf' . $i);
        }
        
        return $exhibits;
	}
	
	protected function _createNewExhibitSection($exhibit, $title, $description, $slug='', $order=1)
	{
	    $exhibitSection = new ExhibitSection;
	    $exhibitSection->exhibit_id = $exhibit->id;
	    $exhibitSection->title = $title;
	    $exhibitSection->description = $description;
	    $exhibitSection->order = $order;

        if ($slug != '') {
            $exhibitSection->slug = $slug;
        }

	    $exhibitSection->save();
	    	    
	    return $exhibitSection;
	}
	
	protected function _createNewExhibitPage($exhibitSection, $title, $slug='', $order=1, $layout='text')
	{
	    $exhibitPage = new ExhibitPage;
	    $exhibitPage->section_id = $exhibitSection->id;
	    $exhibitPage->title = $title;
	    $exhibitPage->layout = $layout;
	    $exhibitPage->order = $order;

        if ($slug != '') {
            $exhibitPage->slug = $slug;
        }

	    $exhibitPage->save();
	    	    
	    return $exhibitPage;
	}
}