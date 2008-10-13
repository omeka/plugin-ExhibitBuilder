<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @subpackage ExhibitBuilder
 **/
define('EXHIBIT_BUILDER_VERSION', '0.2');
define('EXHIBIT_PLUGIN_DIR', dirname(__FILE__));

define('WEB_EXHIBIT_PLUGIN_DIR', WEB_PLUGIN . '/' . basename(dirname(__FILE__)));
define('WEB_EXHIBIT_THEMES', WEB_EXHIBIT_PLUGIN_DIR . '/views/shared/exhibit_themes');
define('EXHIBIT_THEMES_DIR', EXHIBIT_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'views'
. DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'exhibit_themes');
define('EXHIBIT_LAYOUTS_DIR', EXHIBIT_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'views'
. DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'exhibit_layouts');

add_plugin_directories();

add_plugin_hook('install', 'exhibit_builder_install');
add_plugin_hook('define_acl', array('ExhibitBuilderBootstrap', 'setupAcl'));
add_plugin_hook('define_routes', array('ExhibitBuilderBootstrap', 'addRoutes'));
add_plugin_hook('public_theme_header', 'exhibit_public_header');
add_plugin_hook('admin_theme_header', 'exhibit_admin_header');

add_filter('public_navigation_main', 'exhibit_builder_public_main_nav');
add_filter('admin_navigation_main', 'exhibit_admin_nav');
add_filter('define_action_contexts', array('ExhibitBuilderBootstrap', 'defineActionResponseContexts'));

// Helper functions for exhibits
require_once EXHIBIT_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'ExhibitFunctions.php';

function exhibit_builder_install() {
	set_option('exhibit_builder_version', EXHIBIT_BUILDER_VERSION);
	
	$db = get_db();
	$db->exec("CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibits` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `title` varchar(255) collate utf8_unicode_ci default NULL,
      `description` text collate utf8_unicode_ci,
      `credits` text collate utf8_unicode_ci,
      `featured` tinyint(1) default '0',
      `public` tinyint(1) default '0',
      `theme` varchar(30) collate utf8_unicode_ci default NULL,
      `slug` varchar(30) collate utf8_unicode_ci default NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `slug` (`slug`),
      KEY `public` (`public`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->exec("CREATE TABLE IF NOT EXISTS `{$db->prefix}sections` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `title` varchar(255) collate utf8_unicode_ci default NULL,
      `description` text collate utf8_unicode_ci,
      `exhibit_id` int(10) unsigned NOT NULL,
      `order` tinyint(3) unsigned NOT NULL,
      `slug` varchar(30) collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `slug` (`slug`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->exec("CREATE TABLE IF NOT EXISTS `{$db->prefix}items_section_pages` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `item_id` int(10) unsigned default NULL,
      `page_id` int(10) unsigned NOT NULL,
      `text` text collate utf8_unicode_ci,
      `order` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    
    $db->exec("CREATE TABLE IF NOT EXISTS `{$db->prefix}section_pages` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `section_id` int(10) unsigned NOT NULL,
      `title` varchar(255) collate utf8_unicode_ci default NULL,
      `slug` varchar(30) collate utf8_unicode_ci NOT NULL,
      `layout` varchar(255) collate utf8_unicode_ci default NULL,
      `order` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

}

function exhibit_admin_nav($navArray)
{
    if (has_permission('Exhibits', 'browse')) {
        
        $exhibitNav = array('Exhibits'=> uri('exhibits'));
        
        // Put the navigation at the beginning.
        // $navArray = $exhibitNav + $navArray;
        
        // Put the navigation at the end.
        // $navArrray += $exhibitNav;
        
        // Put the navigation 3 spots in.
        $navArray = array_slice($navArray, 0, 3) + $exhibitNav + array_slice($navArray, 3);
    }
    
    return $navArray;
}

function exhibit_builder_public_main_nav($navArray) {
    $navArray['Browse Exhibits'] = uri('exhibits');
    return $navArray;
}

function exhibit_public_header()
{
    // Add the stylesheet for the layout
    echo '<link rel="stylesheet" media="screen" href="' . layout_css() . '" /> ';
}

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

class ExhibitBuilderBootstrap
{
    /**
     * Modify the ACL to include an 'Exhibits' resource.
     * 
     * @return void
     **/
    public static function setupAcl($acl)
    {
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