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
define('WEB_EXHIBIT_PLUGIN_DIR', WEB_PLUGIN . '/' . basename(dirname(__FILE__)));

/**
 * @todo Deprecate these defined constants in favor of a more programmatic way
 * of accessing exhibit themes?
 */
define('EXHIBIT_THEMES_DIR', EXHIBIT_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'views'
. DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'exhibit_themes');

define('EXHIBIT_LAYOUTS_DIR', EXHIBIT_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'views'
. DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'exhibit_layouts');

define('WEB_EXHIBIT_THEMES', WEB_EXHIBIT_PLUGIN_DIR . '/views/shared/exhibit_themes');

// Helper functions for exhibits
require_once EXHIBIT_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'ExhibitFunctions.php';

// Add navigation for the Exhibits
add_filter('admin_navigation_main', 'exhibit_admin_nav');
function exhibit_admin_nav($navArray)
{
    if (has_permission('Exhibits', 'browse')) {
        
        $exhibitNav = array('Exhibits'=> url_for('exhibits'));
        
        // Put the navigation at the beginning.
        // $navArray = $exhibitNav + $navArray;
        
        // Put the navigation at the end.
        // $navArrray += $exhibitNav;
        
        // Put the navigation 3 spots in.
        $navArray = array_slice($navArray, 0, 3) + $exhibitNav + array_slice($navArray, 3);
    }
    
    return $navArray;
}

// Add the CSS for the layout to the public theme's header script
// @todo Change 'theme_header' to 'public_theme_header'
add_plugin_hook('public_theme_header', 'exhibit_public_header');
function exhibit_public_header()
{
    // Add the stylesheet for the layout
    echo '<link rel="stylesheet" media="screen" href="' . layout_css() . '" /> ';
}

// Add the exhibits.css stylesheet to the admin theme, but only for the exhibits pages
add_plugin_hook('admin_theme_header', 'exhibit_admin_header');
function exhibit_admin_header()
{
    // Add the stylesheet for general display of exhibits   
    echo '<link rel="stylesheet" media="screen" href="' . css('exhibits') . '" /> ';
    
}

// Helper for retrieving metadata for a random featured exhibit
/* function random_featured_exhibit()
{
    trigger_error('random_featured_exhibit() will not work until the new Exhibit builder is finished!'); 
    //return get_db()->getTable('Exhibit')->findRandomFeatured();
} */


add_plugin_hook('initialize', array('ExhibitBuilderBootstrap', 'setup'));
add_plugin_hook('add_routes', array('ExhibitBuilderBootstrap', 'addRoutes'));
add_filter('define_action_contexts', array('ExhibitBuilderBootstrap', 'defineActionResponseContexts'));
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
     
     public static function defineActionResponseContexts($contextsArray, $controller, $contextSwitcher)
     {
         switch (get_class($controller)) {
            case 'ExhibitsController':
                $contextsArray['save'] = array('json');
                $contextsArray['add'] = array('json');
                break;
            
            default:
                break;
         }
         
         return $contextsArray;
     }
}














