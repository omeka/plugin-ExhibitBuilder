<?php

/**
 * Installs the plugin, creating the tables in the database and setting plugin options
 * 
 * @return void
 **/
function exhibit_builder_install() 
{	
	$db = get_db();
	$db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibits` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `title` varchar(255) collate utf8_unicode_ci default NULL,
      `description` text collate utf8_unicode_ci,
      `credits` text collate utf8_unicode_ci,
      `featured` tinyint(1) default '0',
      `public` tinyint(1) default '0',
      `theme` varchar(30) collate utf8_unicode_ci default NULL,
      `theme_options` text collate utf8_unicode_ci default NULL,
      `slug` varchar(30) collate utf8_unicode_ci default NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `slug` (`slug`),
      KEY `public` (`public`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}sections` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `title` varchar(255) collate utf8_unicode_ci default NULL,
      `description` text collate utf8_unicode_ci,
      `exhibit_id` int(10) unsigned NOT NULL,
      `order` tinyint(3) unsigned NOT NULL,
      `slug` varchar(30) collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `slug` (`slug`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}items_section_pages` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `item_id` int(10) unsigned default NULL,
      `page_id` int(10) unsigned NOT NULL,
      `text` text collate utf8_unicode_ci,
      `caption` text collate utf8_unicode_ci,
      `order` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    
    $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}section_pages` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `section_id` int(10) unsigned NOT NULL,
      `title` varchar(255) collate utf8_unicode_ci NOT NULL,
      `slug` varchar(30) collate utf8_unicode_ci NOT NULL,
      `layout` varchar(255) collate utf8_unicode_ci default NULL,
      `order` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


    // Legacy upgrade code
    $checkIfTitleExists = $db->fetchOne("SHOW COLUMNS FROM `{$db->prefix}section_pages` LIKE 'title'");
     
    if ($checkIfTitleExists == null) {
        $db->query("ALTER TABLE `{$db->prefix}section_pages` ADD `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, ADD `slug` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
        $newSectionPages = $db->fetchAll("SELECT * FROM `{$db->prefix}section_pages`");

        foreach($newSectionPages as $newPage) {
            $pageNum = $newPage['order'];
            $pageTitle = 'Page '. $pageNum;
            $slug = generate_slug($pageTitle); 
            $id = $newPage['id'];
            $db->query("UPDATE `{$db->prefix}section_pages` SET title='$pageTitle', slug='$slug' WHERE id='$id'");
        }
    }
    
    // Set the initial options
    set_option('exhibit_builder_use_browse_exhibits_for_homepage', '0');
}

/**
 * Uninstalls the plugin, deleting the tables from the database, as well as any plugin options
 * 
 * @return void
 **/
function exhibit_builder_uninstall() 
{   
    // drop the tables
    $db = get_db();
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibits`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}sections`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}items_section_pages`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}section_pages`";
    $db->query($sql);
    
    // delete plugin options
    delete_option('exhibit_builder_use_browse_exhibits_for_homepage');
    delete_option('exhibit_builder_sort_browse');
}

/**
 * Upgrades ExhibitBuilder's tables to be compatible with a new version.
 *
 * @param string $oldVersion Previous plugin version
 * @param string $newVersion Current version; to be upgraded to
 */
function exhibit_builder_upgrade($oldVersion, $newVersion)
{
    // Transition to upgrade model for EB
    if (version_compare($oldVersion, '0.6', '<') )
    {
        $db = get_db();
        $sql = "ALTER TABLE `{$db->prefix}exhibits` ADD COLUMN `theme_options` text collate utf8_unicode_ci default NULL AFTER `theme`";
        $db->query($sql);
    }
    
    if (version_compare($oldVersion, '0.6', '<=') )
    {
        $db = get_db();
        $sql = "ALTER TABLE `{$db->prefix}items_section_pages` ADD COLUMN `caption` text collate utf8_unicode_ci default NULL AFTER `text`";
        $db->query($sql);
    }
}

/**
 * Modify the ACL to include an 'ExhibitBuilder_Exhibits' resource.  
 * 
 * Requires the module name as part of the ACL resource in order to avoid naming
 * conflicts with pre-existing controllers, e.g. an ExhibitBuilder_ItemsController
 * would not rely on the existing Items ACL resource.
 * 
 * @return void
 **/
function exhibit_builder_setup_acl($acl)
{
    /*
     * NOTE: unless explicitly denied, super users and admins have access to all
     * of the defined resources and privileges.  Other user levels will not by default.
     * That means that admin and super users can both manipulate exhibits completely,
     * but researcher/contributor cannot. 
     */
    $resourceList = array('ExhibitBuilder_Exhibits' => array(
        'add', 'editSelf', 'editAll', 'deleteSelf', 'deleteAll',
        'showNotPublic', 'add-page', 'edit-page-content',
        'edit-page-metadata', 'delete-page', 'add-section',
        'edit-section', 'delete-section', 'section-list', 'page-list', 
        'tags')
    );
    $acl->loadResourceList($resourceList);

    $acl->allow(null, 'ExhibitBuilder_Exhibits',
        array('show', 'summary', 'showitem', 'browse', 'tags'));

    // Allow contributors everything but editAll and deleteAll.
    $acl->deny('contributor', 'ExhibitBuilder_Exhibits',
        array('editAll', 'deleteAll'));
    $acl->allow('contributor', 'ExhibitBuilder_Exhibits');
}

/**
 * Add the routes from routes.ini in this plugin folder.
 * 
 * @return void
 **/
function exhibit_builder_routes($router) 
{
     $router->addConfig(new Zend_Config_Ini(EXHIBIT_PLUGIN_DIR .
     DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
}

/**
 * Displays the CSS layout for the exhibit in the header
 * 
 * @return void
 **/
function exhibit_builder_public_header()
{
    if ($layoutCssHref = exhibit_builder_layout_css()) {
        // Add the stylesheet for the layout
        echo '<link rel="stylesheet" media="screen" href="' . html_escape($layoutCssHref) . '" /> ';
    }
}

/**
 * Displays the CSS style and javascript for the exhibit in the admin header
 * 
 * @return void
 **/
function exhibit_builder_admin_header($request)
{
    $module = $request->getModuleName();
    $controller = $request->getControllerName();

    // Check if using Exhibits controller, and add the stylesheet for general display of exhibits
    if ($module == 'exhibit-builder' && $controller == 'exhibits') {
        queue_css('exhibits', 'screen');
        queue_js(array('tiny_mce/tiny_mce', 'exhibits'));
    } else if ($module == 'default' && $controller == 'index') {
        queue_css('exhibits-dashboard');
    }
}

/**
 * Appends an Exhibits section to admin dashboard
 * s
 * @return void
 **/
function exhibit_builder_dashboard()
{
?>
    <?php if (has_permission('ExhibitBuilder_Exhibits','browse')): ?>
	<dt class="exhibits"><a href="<?php echo html_escape(uri('exhibits')); ?>"><?php echo __('Exhibits'); ?></a></dt>
	<dd class="exhibits">
		<ul>
			<li><a class="browse-exhibits" href="<?php echo html_escape(uri('exhibits')); ?>"><?php echo __('Browse Exhibits'); ?></a></li>
			<li><a class="add-exhibit" href="<?php echo html_escape(uri('exhibits/add/')); ?>"><?php echo __('Create an Exhibit'); ?></a></li>
		</ul>
		<p><?php echo __('Create and manage exhibits that display items from the archive.'); ?></p>
	</dd>
	<?php endif;
}

/**
 * Adds the Browse Exhibits link to the public main navigation
 *
 * @param array $navArray The array of navigation links 
 * @return array
 **/
function exhibit_builder_public_main_nav($navArray) 
{
    $navArray[__('Browse Exhibits')] = uri('exhibits');
    return $navArray;
}

/**
 * Adds the Exhibits link to the admin navigation
 *
 * @param array $navArray The array of admin navigation links  
 * @return array
 **/
function exhibit_builder_admin_nav($navArray)
{
    if (has_permission('ExhibitBuilder_Exhibits', 'browse')) {
        $navArray += array(__('Exhibits') => uri('exhibits'));
    }
    return $navArray;
}

/**
 * Intercepts get_theme_option calls to allow theme settings on a per-Exhibit basis.
 *
 * @param string $themeOptions Serialized array of theme options
 * @param string $themeName Name of theme to get options for (ignored by ExhibitBuilder)
 */
function exhibit_builder_theme_options($themeOptions, $themeName)
{
    if (Omeka_Context::getInstance()->getRequest()->getModuleName() == 'exhibit-builder' && function_exists('__v')) {
        if ($exhibit = exhibit_builder_get_current_exhibit()) {
            $exhibitThemeOptions = $exhibit->getThemeOptions();
        }
    }
    if (!empty($exhibitThemeOptions)) {
        return serialize($exhibitThemeOptions);
    }
    return $themeOptions;
}

/**
 * Filter for changing the public theme between exhibits.
 *
 * @param string $themeName "Normal" current theme.
 * @return string Theme that will actually be used.
 */
function exhibit_builder_public_theme_name($themeName)
{
    static $exhibitTheme;

    if ($exhibitTheme) {
        return $exhibitTheme;
    }

    $request = Zend_Controller_Front::getInstance()->getRequest();
    
    if ($request->getModuleName() == 'exhibit-builder') {
        $slug = $request->getParam('slug');
        $exhibit = get_db()->getTable('Exhibit')->findBySlug($slug);
        if ($exhibit && ($exhibitTheme = $exhibit->theme)) {
            return $exhibitTheme;
        }
    }
    return $themeName;
}

/**
 * Custom hook from the HtmlPurifier plugin that will only fire when that plugin is
 * enabled.
 * 
 * @param Zend_Controller_Request_Http $request
 * @param HTMLPurifier $purifier The purifier object that was built from the configuration
 * provided on the configuration form of the HtmlPurifier plugin.  
 * @return void
 **/
function exhibit_builder_purify_html($request, $purifier)
{
    // Make sure that we only bother with the Exhibits controller in the ExhibitBuilder module.
    if ($request->getControllerName() != 'exhibits' or $request->getModuleName() != 'exhibit-builder') {
        return;
    }
    
    $post = $request->getPost();
    
    switch ($request->getActionName()) {
        // exhibit-metadata-form
        case 'add':
        case 'edit':
        
        // section-metadata-form    
        case 'add-section':
        case 'edit-section':
            // The description field on both of these forms should be HTML.
            $post['description'] = $purifier->purify($post['description']);
            break;

        case 'add-page':
        case 'edit-page-metadata':
            // Skip the page-metadata-form.
            break;
        
        case 'edit-page-content':
            // page-content-form
            if (isset($post['Text']) && is_array($post['Text'])) {
                // All of the 'Text' entries are HTML.
                foreach ($post['Text'] as $key => $text) {
                    $post['Text'][$key] = $purifier->purify($text);
                }            
            }
            if (isset($post['Caption']) && is_array($post['Caption'])) {
                foreach ($post['Caption'] as $key => $text) {
                    $post['Caption'][$key] = $purifier->purify($text);
                }
            }
            break;
        
        default:
            // Don't process anything by default.
            break;
    }
    
    $request->setPost($post);
}

/**
 * Returns the select dropdown for the exhibits 
 * 
 * @param array $props Optional
 * @param string|null $value Optional
 * @param string|null $label Optional
 * @param array $search Optional
 * @return string
 **/
function exhibit_builder_select_exhibit($props = array(), $value = null, $label = null, $search = array())
{
    return _select_from_table('Exhibit', $props, $value, $label, $search);
}

function exhibit_builder_config_form()
{
    include 'config_form.php';
}

function exhibit_builder_config()
{
    set_option('exhibit_builder_use_browse_exhibits_for_homepage', (int)(boolean)$_POST['exhibit_builder_use_browse_exhibits_for_homepage']);
    set_option('exhibit_builder_sort_browse', $_POST['exhibit_builder_sort_browse']);
}

function exhibit_builder_initialize()
{
    add_translation_source(dirname(__FILE__) . '/languages');
    Zend_Controller_Front::getInstance()->registerPlugin(new ExhibitBuilderControllerPlugin);
}

/**
 * Hooks into item_browse_sql to return items in a particular exhibit. The
 * passed exhibit can either be an Exhibit object or a specific exhibit ID.
 *
 * @return Omeka_Db_Select
 */
function exhibit_builder_item_browse_sql($select, $params)
{
    $db = get_db();

    if ($request = Zend_Controller_Front::getInstance()->getRequest()) {
        $exhibit = $request->get('exhibit') ? $request->get('exhibit') : null;
    }

    $exhibit = isset($params['exhibit']) ? $params['exhibit'] : $exhibit;

    if ($exhibit) {
        $select->joinInner(
            array('isp' => $db->ExhibitPageEntry),
            'isp.item_id = i.id',
            array()
            );

        $select->joinInner(
            array('sp' => $db->ExhibitPage),
            'sp.id = isp.page_id',
            array()
            );

        $select->joinInner(
            array('s' => $db->ExhibitSection),
            's.id = sp.section_id',
            array()
            );

        $select->joinInner(
            array('e' => $db->Exhibit),
            'e.id = s.exhibit_id',
            array()
            );

        if ($exhibit instanceof Exhibit) {
            $select->where('e.id = ?', $exhibit->id);
        } elseif (is_numeric($exhibit)) {
            $select->where('e.id = ?', $exhibit);
        }
    }

    return $select;
}

/**
 * Form element for advanced search.
 */
function exhibit_builder_append_to_advanced_search()
{
    $html = '<div class="field">'
          . __v()->formLabel('exhibit', __('Search by Exhibit'))
          . '<div class="inputs">'
          . _select_from_table('Exhibit', array('name' => 'exhibit'))
          . '</div></div>';
    echo $html;
}

class ExhibitBuilderControllerPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
    *
    * @param Zend_Controller_Request_Abstract $request
    * @return void
    */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $router = Omeka_Context::getInstance()->getFrontController()->getRouter();
        if (get_option('exhibit_builder_use_browse_exhibits_for_homepage') == '1' && !is_admin_theme()) {
            $router->addRoute(
                'exhibit_builder_show_home_page', 
                new Zend_Controller_Router_Route(
                    '/:page', 
                    array(
                        'module'       => 'exhibit-builder', 
                        'controller'   => 'exhibits', 
                        'action'       => 'browse',
                        'page'         => 1 
                    ),
                    array(
                        'page'  => '\d+'
                    )
                )
            );
        } 
    }
}
