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
      `added` timestamp NOT NULL default '0000-00-00 00:00:00',
      `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
      `owner_id` int unsigned default NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `slug` (`slug`),
      KEY `public` (`public`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


    $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_page_entries` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `item_id` int(10) unsigned default NULL,
      `page_id` int(10) unsigned NOT NULL,
      `text` text collate utf8_unicode_ci,
      `caption` text collate utf8_unicode_ci,
      `order` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_pages` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `exhibit_id` int(10) unsigned NOT NULL,
      `parent_id` int(10) unsigned,
      `title` varchar(255) collate utf8_unicode_ci NOT NULL,
      `slug` varchar(30) collate utf8_unicode_ci NOT NULL,
      `layout` varchar(255) collate utf8_unicode_ci default NULL,
      `order` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

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
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibit_page_entries`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibit_pages`";
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
function exhibit_builder_upgrade($args)
{
    $oldVersion = $args['old_version'];
    $newVersion = $args['new_version'];
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

    if(version_compare($oldVersion, '2.0', '<')) {
        $db = get_db();

        $sql = "RENAME TABLE `{$db->prefix}items_section_pages` TO `{$db->prefix}exhibit_page_entries` ";
        $db->query($sql);


        //alter the section_pages table into revised exhibit_pages table
        $sql = "ALTER TABLE `{$db->prefix}section_pages` ADD COLUMN `parent_id` INT UNSIGNED NULL AFTER `id` ";
        $db->query($sql);

        $sql = "ALTER TABLE `{$db->prefix}section_pages` ADD COLUMN `exhibit_id` INT UNSIGNED NOT NULL AFTER `parent_id` ";
        $db->query($sql);

        $sql = "RENAME TABLE `{$db->prefix}section_pages` TO `{$db->prefix}exhibit_pages` ";
        $db->query($sql);

        //dig up all the data about sections so I can turn them into ExhibitPages
        $sql = "SELECT * FROM `{$db->prefix}sections` ";
        $result = $db->query($sql);
        $sectionData = $result->fetchAll();

        $sectionIdMap = array();
        foreach($sectionData as $section) {
            $sectionToPage = new ExhibitPage();
            $sectionToPage->title = $section['title'];
            $sectionToPage->parent_id = null;
            $sectionToPage->exhibit_id = $section['exhibit_id'];
            $sectionToPage->layout = 'text';
            $sectionToPage->slug = $section['slug'];
            $sectionToPage->order = $section['order'];
            $sectionToPage->save();
            $sectionIdMap[$section['id']] = array('pageId' =>$sectionToPage->id, 'exhibitId'=>$section['exhibit_id']);

            //slap the section's description into a text entry for the page
            $entry = new ExhibitPageEntry();
            $entry->page_id = $sectionToPage->id;
            $entry->order = 1;
            $entry->text = $section['description'];
            $entry->save();
        }


        //map the old section ids to the new page ids, and slap in the correct exhibit id.
        foreach($sectionIdMap as $sectionId=>$data) {
            $pageId = $data['pageId'];
            $exhibitId = $data['exhibitId'];
            //probably a more sophisticated way to do the updates, but my SQL skills aren't up to it
            $sql = "UPDATE `{$db->prefix}exhibit_pages` SET parent_id = $pageId, exhibit_id = $exhibitId WHERE section_id = $sectionId ";
            $db->query($sql);
        }

        $sql = "ALTER TABLE `{$db->prefix}exhibit_pages` DROP `section_id` ";

        $db->query($sql);

        //finally kill the sections for good. Kill pussycat! Kill!
        $sql = "DROP TABLE `{$db->prefix}sections`";

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
function exhibit_builder_setup_acl($args)
{
    $acl = $args['acl'];
    /*
     * NOTE: unless explicitly denied, super users and admins have access to all
     * of the defined resources and privileges.  Other user levels will not by default.
     * That means that admin and super users can both manipulate exhibits completely,
     * but researcher/contributor cannot.
     */
    $acl->addResource('ExhibitBuilder_Exhibits');

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
function exhibit_builder_routes($args)
{
    $router = $args['router'];
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
    queue_css('exhibits');
}

/**
 * Displays the CSS style and javascript for the exhibit in the admin header
 *
 * @return void
 **/
function exhibit_builder_admin_header($args)
{
    $request = $args['request'];
    $module = $request->getModuleName();
    $controller = $request->getControllerName();

    // Check if using Exhibits controller, and add the stylesheet for general display of exhibits
    if ($module == 'exhibit-builder' && $controller == 'exhibits') {
        queue_css('exhibits', 'screen');
        queue_js(array('tiny_mce/tiny_mce', 'exhibits'));
        queue_js('tree.jquery');
        queue_css('jqtree');
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
function exhibit_builder_theme_options($themeOptions, $args)
{
    $themeName = $args['theme_name'];
    if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() == 'exhibit-builder' && function_exists('__v')) {
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
function exhibit_builder_purify_html($args)
{
    $request = $args['request'];
    $purifier = $args['purifier'];
    // Make sure that we only bother with the Exhibits controller in the ExhibitBuilder module.
    if ($request->getControllerName() != 'exhibits' or $request->getModuleName() != 'exhibit-builder') {
        return;
    }

    $post = $request->getPost();

    switch ($request->getActionName()) {
        // exhibit-metadata-form
        case 'add':
        case 'edit':

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
function exhibit_builder_item_browse_sql($args)
{
    $select = $args['select'];
    $params = $args['params'];
    $db = get_db();

    if ($request = Zend_Controller_Front::getInstance()->getRequest()) {
        $exhibit = $request->get('exhibit') ? $request->get('exhibit') : null;
    }

    $exhibit = isset($params['exhibit']) ? $params['exhibit'] : $exhibit;

    if ($exhibit) {
        $select->joinInner(
            array('epe' => $db->ExhibitPageEntry),
            'epe.item_id = items.id',
            array()
            );

        $select->joinInner(
            array('ep' => $db->ExhibitPage),
            'ep.id = epe.page_id',
            array()
            );

        $select->joinInner(
            array('e' => $db->Exhibit),
            'e.id = ep.exhibit_id',
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
    $view = __v();
    $html = '<div class="field">'
          . $view->formLabel('exhibit', __('Search by Exhibit'))
          . '<div class="inputs">'
          . $view->formSelect('exhibit', @$_GET['exhibit'], array(), get_table_options('Exhibit'))
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
        $router = Zend_Registry::get('bootstrap')->getResource('Router');
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
