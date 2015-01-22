<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Add EB's translations directory for all requests.
 */
function exhibit_builder_initialize()
{
    add_translation_source(dirname(__FILE__) . '/languages');
    add_shortcode ('exhibits', 'exhibit_builder_exhibits_shortcode');
    add_shortcode ('featured_exhibits', 'exhibit_builder_featured_exhibits_shortcode');
}

/**
 * Install the plugin, creating the tables in the database.
 */
function exhibit_builder_install()
{
    $db = get_db();

    $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibits` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) DEFAULT NULL,
    `description` TEXT,
    `credits` TEXT,
    `featured` TINYINT(1) DEFAULT 0,
    `public` TINYINT(1) DEFAULT 0,
    `theme` VARCHAR(30) DEFAULT NULL,
    `theme_options` TEXT,
    `slug` VARCHAR(30) NOT NULL,
    `added` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `use_summary_page` TINYINT(1) DEFAULT 1,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `public` (`public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL
    );

    $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_pages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `exhibit_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(30) NOT NULL,
    `order` SMALLINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY  (`id`),
    KEY `exhibit_id_order` (`exhibit_id`, `order`),
    UNIQUE KEY `exhibit_id_parent_id_slug` (`exhibit_id`, `parent_id`, `slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL
    );

    $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_page_blocks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_id` INT UNSIGNED NOT NULL,
    `layout` VARCHAR(50) NOT NULL,
    `options` TEXT,
    `text` MEDIUMTEXT,
    `order` SMALLINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `page_id_order` (`page_id`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL
    );

    $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_block_attachments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `file_id` INT UNSIGNED DEFAULT NULL,
    `caption` TEXT,
    `order` SMALLINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `block_id_order` (`block_id`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL
    );
}

/**
 * Uninstall the plugin.
 */
function exhibit_builder_uninstall()
{
    // drop the tables
    $db = get_db();
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibits`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibit_pages`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibit_page_blocks`";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}exhibit_block_attachments`";
    $db->query($sql);

    // delete plugin options
    delete_option('exhibit_builder_sort_browse');
}

/**
 * Upgrades ExhibitBuilder's tables to be compatible with a new version.
 *
 * @param array $args expected keys:
 *  'old_version' => Previous plugin version
 *  'new_version' => Current version; to be upgraded to
 */
function exhibit_builder_upgrade($args)
{
    $oldVersion = $args['old_version'];
    $newVersion = $args['new_version'];

    $db = get_db();

    // Transition to upgrade model for EB
    if (version_compare($oldVersion, '0.6', '<') )
    {
        $sql = "ALTER TABLE `{$db->prefix}exhibits` ADD COLUMN `theme_options` text collate utf8_unicode_ci default NULL AFTER `theme`";
        $db->query($sql);
    }

    if (version_compare($oldVersion, '0.6', '<=') )
    {
        $sql = "ALTER TABLE `{$db->prefix}items_section_pages` ADD COLUMN `caption` text collate utf8_unicode_ci default NULL AFTER `text`";
        $db->query($sql);
    }

    if(version_compare($oldVersion, '2.0-dev', '<')) {
        // Automatically skip these steps if the final one is already done
        // (would happen on retry of an earlier failed upgrade)
        $renamesDone = (bool) $db->fetchOne("SHOW TABLES LIKE '{$db->prefix}exhibit_pages'");
        if (!$renamesDone) {
            $sql = "RENAME TABLE `{$db->prefix}items_section_pages` TO `{$db->prefix}exhibit_page_entries`";
            $db->query($sql);

            //alter the section_pages table into revised exhibit_pages table
            $sql = "ALTER TABLE `{$db->prefix}section_pages` ADD COLUMN `parent_id` INT UNSIGNED NULL AFTER `id`";
            $db->query($sql);

            $sql = "ALTER TABLE `{$db->prefix}section_pages` ADD COLUMN `exhibit_id` INT UNSIGNED NOT NULL AFTER `parent_id`";
            $db->query($sql);

            $sql = "RENAME TABLE `{$db->prefix}section_pages` TO `{$db->prefix}exhibit_pages`";
            $db->query($sql);
        }

        // Remove any broken pages or artifacts from a failed upgrade
        $sql = "DELETE FROM `{$db->prefix}exhibit_pages` WHERE section_id = 0";
        $db->query($sql);

        // Get all the data about sections to turn them into ExhibitPages
        $sql = "SELECT * FROM `{$db->prefix}sections` ";
        $result = $db->query($sql);
        $sectionData = $result->fetchAll();

        $sectionIdMap = array();
        foreach($sectionData as $section) {
            // Create a page for each section
            $newPageData = array(
                'title' => $section['title'],
                'parent_id' => null,
                'exhibit_id' => $section['exhibit_id'],
                'section_id' => 0,
                'layout' => 'text',
                'slug' => $section['slug'],
                'order' => $section['order']
            );
            $db->getAdapter()->insert($db->ExhibitPage, $newPageData);
            $pageId = (int) $db->lastInsertId();

            $sectionIdMap[$section['id']] = array('pageId' => $pageId, 'exhibitId' => $section['exhibit_id']);

            //slap the section's description into a text entry for the page
            $newEntryData = array(
                'page_id' => $pageId,
                'order' => 1,
                'text' => $section['description']
            );
            $db->getAdapter()->insert($db->ExhibitPageEntry, $newEntryData);
        }

        //map the old section ids to the new page ids, and slap in the correct exhibit id.
        foreach($sectionIdMap as $sectionId => $data) {
            $updateData = array(
                'parent_id' => $data['pageId'],
                'exhibit_id' => $data['exhibitId']
            );
            $db->update($db->ExhibitPage, $updateData,
                array('section_id = ?' => $sectionId));
        }

        $sql = "ALTER TABLE `{$db->prefix}exhibit_pages` DROP `section_id` ";
        $db->query($sql);

        //finally kill the sections for good.
        $sql = "DROP TABLE `{$db->prefix}sections`";
        $db->query($sql);
    }

    if(version_compare($oldVersion, '2.0-dev2', '<')) {
        $sql = "ALTER TABLE `{$db->prefix}exhibit_page_entries` ADD `file_id` INT UNSIGNED DEFAULT NULL AFTER `item_id`";
        $db->query($sql);

        $sql = "ALTER TABLE `{$db->prefix}exhibit_page_entries` ADD INDEX `page_id_order` (`page_id`, `order`)";
        $db->query($sql);

        $sql = "ALTER TABLE `{$db->prefix}exhibit_pages` ADD INDEX `exhibit_id_order` (`exhibit_id`, `order`)";
        $db->query($sql);

        delete_option('exhibit_builder_use_browse_exhibits_for_homepage');
    }

    if (version_compare($oldVersion, '2.0', '<=')) {
        $sql = "ALTER TABLE `{$db->prefix}exhibit_pages` ADD UNIQUE INDEX `exhibit_id_parent_id_slug` (`exhibit_id`, `parent_id`, `slug`)";
        $db->query($sql);
    }

    if (version_compare($oldVersion, '3.0-dev', '<')) {
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_page_blocks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_id` INT UNSIGNED NOT NULL,
    `layout` VARCHAR(50) NOT NULL,
    `options` TEXT,
    `text` MEDIUMTEXT,
    `order` SMALLINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `page_id_order` (`page_id`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL
        );

        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}exhibit_block_attachments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `file_id` INT UNSIGNED DEFAULT NULL,
    `caption` TEXT,
    `order` SMALLINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `block_id_order` (`block_id`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL
        );

        $sql = "SELECT id, layout FROM `{$db->prefix}exhibit_pages` ORDER BY id";
        $pages = $db->query($sql)->fetchAll();

        $upgrader = new ExhibitPageUpgrader($db);
        foreach ($pages as $page) {
            $upgrader->upgradePage($page['id'], $page['layout']);
        }

        $sql = "DROP TABLE `{$db->prefix}exhibit_page_entries`";
        $db->query($sql);

        $sql = "ALTER TABLE `{$db->prefix}exhibit_pages` DROP COLUMN `layout`";
        $db->query($sql);
    }

    if (version_compare($oldVersion, '3.1.4', '<')) {
        $sql = "ALTER TABLE `{$db->prefix}exhibits` ADD `use_summary_page` TINYINT(1) DEFAULT 1 AFTER `owner_id`";
        $db->query($sql);
    }
}

/**
 * Display the configuration form.
 */
function exhibit_builder_config_form()
{
    include 'config_form.php';
}

/**
 * Process the configuration form.
 */
function exhibit_builder_config()
{
    set_option('exhibit_builder_sort_browse', $_POST['exhibit_builder_sort_browse']);
}

/**
 * Modify the ACL to include an 'ExhibitBuilder_Exhibits' resource.
 *
 * Requires the module name as part of the ACL resource in order to avoid naming
 * conflicts with pre-existing controllers, e.g. an ExhibitBuilder_ItemsController
 * would not rely on the existing Items ACL resource.
 *
 * @param array $args Zend_Acl in the 'acl' key
 */
function exhibit_builder_define_acl($args)
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
        array('show', 'summary', 'show-item', 'browse', 'tags'));

    // Allow contributors everything but editAll and deleteAll.
    $acl->allow('contributor', 'ExhibitBuilder_Exhibits', array(
        'add', 'add-page', 'delete-confirm', 'edit-page',
        'attachment', 'attachment-item-options', 'theme-config',
        'editSelf', 'deleteSelf', 'showSelfNotPublic', 'block-form'));

    $acl->allow(null, 'ExhibitBuilder_Exhibits', array('edit', 'delete'),
        new Omeka_Acl_Assert_Ownership);
}

/**
 * Add the routes from routes.ini in this plugin folder.
 *
 * @param array $args Router object in 'router' key
 */
function exhibit_builder_define_routes($args)
{
    $router = $args['router'];
    $router->addConfig(new Zend_Config_Ini(EXHIBIT_PLUGIN_DIR .
        DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
}

/**
 * Display the CSS layout for the exhibit in the public head
 */
function exhibit_builder_public_head($args)
{
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $module = $request->getModuleName();

    if ($module == 'exhibit-builder') {
        queue_css_file('exhibits');
        if (($exhibitPage = get_current_record('exhibit_page', false))) {
            $blocks = $exhibitPage->ExhibitPageBlocks;

            $layouts = array();
            foreach ($blocks as $block) {
                $layout = $block->getLayout();
                if (!array_key_exists($layout->id, $layouts)) {
                    $layouts[$layout->id] = true;
                    try {
                        queue_css_url($layout->getAssetUrl('layout.css'));
                    } catch (InvalidArgumentException $e) {
                        // no CSS for this layout
                    }
                }
            }
            fire_plugin_hook('exhibit_builder_page_head', array(
                'view' => $args['view'],
                'layouts' => $layouts)
            );
        }
    }
}

/**
 * Display the CSS style and javascript for the exhibit in the admin head
 */
function exhibit_builder_admin_head()
{
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();

    // Check if using Exhibits controller, and add the stylesheet for general display of exhibits
    if ($module == 'exhibit-builder' && $controller == 'exhibits') {
        queue_css_file('exhibits', 'screen');
        queue_js_file(array('vendor/tiny_mce/tiny_mce', 'exhibits'));
    }
}

/**
 * Append an Exhibits section to admin dashboard
 *
 * @param array $stats Array of "statistics" displayed on dashboard
 * @return array
 */
function exhibit_builder_dashboard_stats($stats)
{
    if (is_allowed('ExhibitBuilder_Exhibits', 'browse')) {
        $stats[] = array(link_to('exhibits', array(), total_records('Exhibits')), __('exhibits'));
    }
    return $stats;
}

/**
 * Adds the Browse Exhibits link to the public main navigation
 *
 * @param array $navArray The array of navigation links
 * @return array
 */
function exhibit_builder_public_main_nav($navArray)
{
    $navArray[] = array(
        'label' => __('Browse Exhibits'),
        'uri' => url('exhibits'),
        'visible' => true
    );
    return $navArray;
}

/**
 * Adds the Exhibits link to the admin navigation
 *
 * @param array $navArray The array of admin navigation links
 * @return array
 */
function exhibit_builder_admin_nav($navArray)
{
    $navArray[] = array(
        'label' => __('Exhibits'),
        'uri' => url('exhibits'),
        'resource' => 'ExhibitBuilder_Exhibits',
        'privilege' => 'browse'
    );
    return $navArray;
}

/**
 * Intercept get_theme_option calls to allow theme settings on a per-Exhibit basis.
 *
 * @param string $themeOptions Serialized array of theme options
 * @param string $args Unused here
 */
function exhibit_builder_theme_options($themeOptions, $args)
{
    try {
        if ($exhibit = get_current_record('exhibit', false)) {
            $exhibitThemeOptions = $exhibit->getThemeOptions();
            if (!empty($exhibitThemeOptions)) {
                return serialize($exhibitThemeOptions);
            }
        }
    } catch (Zend_Exception $e) {
        // no view available
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
        if ($exhibit && $exhibit->theme) {
            // Save result in static for future calls
            $exhibitTheme = $exhibit->theme;
            add_filter('theme_options', 'exhibit_builder_theme_options');
            return $exhibitTheme;
        }
    }

    // Short-circuit any future calls to the hook if we didn't change the theme
    $exhibitTheme = $themeName;
    return $exhibitTheme;
}

/**
 * Custom hook from the HtmlPurifier plugin that will only fire when that plugin is
 * enabled.
 *
 * @param $args: 'purifier' => HTMLPurifier The purifier object.
 */
function exhibit_builder_purify_html($args)
{
    $request = Zend_Controller_Front::getInstance()->getRequest();
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
            if (!empty($post['description'])) {
                $post['description'] = $purifier->purify($post['description']);
            }
            break;
        case 'add-page':
        case 'edit-page':
            // page-content-form
            if (!empty($post['blocks'])) {
                foreach ($post['blocks'] as &$blockData) {
                    if (!empty($blockData['text'])) {
                        $blockData['text'] = $purifier->purify($blockData['text']);
                    }
                    if (!empty($blockData['attachments'])) {
                        foreach ($blockData['attachments'] as &$attachmentData) {
                            if (!empty($attachmentData['caption'])) {
                                $attachmentData['caption'] = $purifier->purify($attachmentData['caption']);
                            }
                        }
                    }
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
 * Hooks into item_browse_sql to return items in a particular exhibit. The
 * passed exhibit can either be an Exhibit object or a specific exhibit ID.
 *
 * @return Omeka_Db_Select
 */
function exhibit_builder_items_browse_sql($args)
{
    $select = $args['select'];
    $params = $args['params'];
    $db = get_db();

    $exhibit = isset($params['exhibit']) ? $params['exhibit'] : null;

    if ($exhibit) {
        $select
            ->joinInner(
                array('eba' => $db->ExhibitBlockAttachment),
                'eba.item_id = items.id',
                array()
            )
            ->joinInner(
                array('epb' => $db->ExhibitPageBlock),
                'epb.id = eba.block_id',
                array()
            )
            ->joinInner(
                array('ep' => $db->ExhibitPage),
                'ep.id = epb.page_id',
                array()
            )
            ->joinInner(
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
function exhibit_builder_items_search()
{
    $view = get_view();
    $html = '<div class="field"><div class="two columns alpha">'
          . $view->formLabel('exhibit', __('Search by Exhibit'))
          . '</div><div class="five columns omega inputs">'
          . $view->formSelect('exhibit', @$_GET['exhibit'], array(), get_table_options('Exhibit'))
          . '</div></div>';
    echo $html;
}

function exhibit_builder_search_record_types($recordTypes)
{
    $recordTypes['Exhibit'] = __('Exhibit');
    $recordTypes['ExhibitPage'] = __('Exhibit Page');
    return $recordTypes;
}

/**
 * Add exhibit title to item search filters.
 */
function exhibit_builder_item_search_filters($displayArray, $args)
{
    $request = $args['request_array'];

    if (isset($request['exhibit'])
        && ($exhibit = get_record_by_id('Exhibit', $request['exhibit']))
    ) {
        $displayArray['exhibit'] =
            metadata($exhibit, 'title', array('no_escape' => true));
    }
    return $displayArray;
}

function exhibit_builder_api_resources($apiResources)
{
    $apiResources['exhibits'] = array(
        'record_type' => 'Exhibit',
        'actions' => array('get', 'index'),
        'index_params' => array('tag', 'tags', 'sort', 'public', 'featured')
    );
    $apiResources['exhibit_pages'] = array(
        'record_type' => 'ExhibitPage',
        'actions' => array('get', 'index'),
        'index_params' => array('parent', 'exhibit', 'order', 'topOnly', 'item')
    );


    return $apiResources;
}

function exhibit_builder_api_extend_items($extend, $args)
{
    $item = $args['record'];
    $pages = get_db()->getTable('ExhibitPage')->findBy(array('item' => $item->id));

    if(count($pages) == 1) {
        $page = $pages[0];
        $extend['exhibit_pages'] = array(
            'id' => $page->id,
            'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl("/exhibit_pages/{$page->id}"),
            'resource' => 'exhibit_pages'
        );
    } else {
        $extend['exhibit_pages'] = array(
            'count' => count($pages),
            'url' => Omeka_Record_Api_AbstractRecordAdapter::getResourceUrl("/exhibit_pages?item={$item->id}"),
            'resource' => 'exhibit_pages'
        );
    }
    return $extend;
}

function exhibit_builder_api_import_omeka_adapters($adapters, $args)
{
        $exhibitsAdapter = new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'Exhibit');
        $exhibitsAdapter->setService($args['omeka_service']);
        $exhibitsAdapter->setUserProperties(array('owner'));
        $adapters['exhibits'] = $exhibitsAdapter;
        $adapters['exhibit_pages'] = 'ExhibitBuilder_ApiImport_ExhibitPageAdapter';
        return $adapters;
}
