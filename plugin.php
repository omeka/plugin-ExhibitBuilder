<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @subpackage ExhibitBuilder
 **/

// @todo Deprecate this!
add_plugin_directories();
 
define('EXHIBIT_BUILDER_VERSION', '0.2');
define('EXHIBIT_PLUGIN_DIR', dirname(__FILE__));

// Add navigation for the Exhibits
add_filter('admin_navigation_main', 'exhibit_admin_nav');
function exhibit_admin_nav($navArray)
{
    if (has_permission('Exhibits', 'browse')) {
        return array('Exhibits'=> url_for('exhibits')) + $navArray;
    }
    
    return $navArray;
}

// Add the CSS for the layout to the public theme's header script
// @todo Change 'theme_header' to 'public_theme_header'
add_filter('theme_header', 'exhibit_add_header');
function exhibit_add_header()
{
    // Add the stylesheet for the layout
    echo '<link rel="stylesheet" media="screen" href="' . layout_css() . '" /> ';
    
}

// Helper for retrieving metadata for a random featured exhibit
/* function random_featured_exhibit()
{
    trigger_error('random_featured_exhibit() will not work until the new Exhibit builder is finished!'); 
    //return get_db()->getTable('Exhibit')->findRandomFeatured();
} */


add_plugin_hook('initialize', array('ExhibitBuilderBootstrap', 'setup'));
add_plugin_hook('add_routes', array('ExhibitBuilderBootstrap', 'addRoutes'));
class ExhibitBuilderBootstrap
{
    /**
     * @todo Separate these actions by priority when priorities are added to plugin hooks.
     **/
    public static function setup()
    {
        self::setupAcl();
    }
    
    /**
     * Modify the ACL to include an 'Exhibits' resource.
     * 
     * @return void
     **/
    public static function setupAcl()
    {
        $acl = Omeka_Context::getInstance()->getAcl();
        $acl->loadResourceList(array('Exhibits'=> array('add', 'edit',
        'delete', 'addPage', 'editPage', 'deletePage', 'addSection', 'editSection',
        'deleteSection', 'save', 'showNotPublic')));    
        
        // Test denying permission to see the exhibits.
        // $acl->deny('super', array('Exhibits'));    
    }
    
    /**
     * Add the routes from routes.ini in this plugin folder.
     * 
     * @return void
     **/
     public static function addRoutes($router)
     {
         $router->addConfig(new Zend_Config_Ini(EXHIBIT_PLUGIN_DIR .
         DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
     }
}














