<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

if (!defined('EXHIBIT_PLUGIN_DIR')) {
    define('EXHIBIT_PLUGIN_DIR', dirname(__FILE__));
}

add_plugin_hook('install', 'exhibit_builder_install');
add_plugin_hook('uninstall', 'exhibit_builder_uninstall');
add_plugin_hook('upgrade', 'exhibit_builder_upgrade');
add_plugin_hook('config_form', 'exhibit_builder_config_form');
add_plugin_hook('config', 'exhibit_builder_config');
add_plugin_hook('initialize', 'exhibit_builder_initialize');
add_plugin_hook('define_acl', 'exhibit_builder_define_acl');
add_plugin_hook('define_routes', 'exhibit_builder_define_routes');
add_plugin_hook('public_head', 'exhibit_builder_public_head');
add_plugin_hook('admin_head', 'exhibit_builder_admin_head');
add_plugin_hook('items_browse_sql', 'exhibit_builder_items_browse_sql');
add_plugin_hook('admin_items_search', 'exhibit_builder_items_search');
add_plugin_hook('public_items_search', 'exhibit_builder_items_search');
add_plugin_hook('html_purifier_form_submission', 'exhibit_builder_purify_html');

add_filter('public_navigation_main', 'exhibit_builder_public_main_nav');
add_filter('admin_navigation_main', 'exhibit_builder_admin_nav');
add_filter('public_theme_name', 'exhibit_builder_public_theme_name');
add_filter('admin_dashboard_stats', 'exhibit_builder_dashboard_stats');
add_filter('search_record_types', 'exhibit_builder_search_record_types');
add_filter('api_resources', 'exhibit_builder_api_resources');
add_filter('api_extend_items', 'exhibit_builder_api_extend_items');
add_filter('item_search_filters', 'exhibit_builder_item_search_filters');
add_filter('api_import_omeka_adapters', 'exhibit_builder_api_import_omeka_adapters');

// Helper functions for exhibits and exhibit pages
require_once EXHIBIT_PLUGIN_DIR . '/helpers/ExhibitFunctions.php';
require_once EXHIBIT_PLUGIN_DIR . '/helpers/ExhibitPageFunctions.php';

require_once EXHIBIT_PLUGIN_DIR . '/functions.php';
