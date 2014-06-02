<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * Exhibit model.
 *
 * @package ExhibitBuilder
 */
class Exhibit extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    /**
     * Exhibit title.
     *
     * @var string
     */
    public $title;

    /**
     * Exhibit description (in HTML).
     *
     * @var string
     */
    public $description;

    /**
     * Exhibit credits.
     *
     * @var string
     */
    public $credits;
    
    /**
     * Whether the exhibit is featured.
     *
     * @var integer
     */
    public $featured = 0;

    /**
     * Whether the exhibit is public.
     *
     * @var integer
     */
    public $public = 0;
    
    /**
     * Public theme to use for this exhibit.
     *
     * @var string
     */
    public $theme;

    /**
     * Options for this exhibit's theme, serialized.
     *
     * @var string
     */
    public $theme_options;
    
    /**
     * URL slug for the exhibit.
     *
     * @var string
     */
    public $slug;
    
    /**
     * Date the exhibit was created, as a MySQL-formatted date string.
     *
     * @var string
     */
    public $added;

    /**
     * Date the exhibit was last modified, as a MySQL-formatted date string.
     *
     * @var string
     */
    public $modified;

    /**
     * User ID of the user who created the exhibit.
     *
     * @var integer
     */
    public $owner_id;

    /**
     * Quick-access mappings for related records.
     *
     * @var array
     */
    protected $_related = array(
        'Pages' => 'getPages', 'TopPages' => 'getTopPages', 'Tags' => 'getTags'
    );

    /**
     * Set up mixins for shared behaviors.
     */
    public function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Tag($this);
        $this->_mixins[] = new Mixin_Owner($this);
        $this->_mixins[] = new Mixin_Slug($this, array(
            'slugEmptyErrorMessage' => __('Exhibits must be given a valid slug.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('Your URL slug is already in use by another exhibit.  Please choose another.')));
        $this->_mixins[] = new Mixin_Timestamp($this);
        $this->_mixins[] = new Mixin_PublicFeatured($this);
        $this->_mixins[] = new Mixin_Search($this);
    }


    /**
     * Validation callback.
     */
    protected function _validate()
    {
        if (!strlen((string)$this->title)) {
            $this->addError('title', __('An exhibit must be given a title.'));
        }

        if (strlen((string)$this->title) > 255) {
            $this->addError('title', __('The title for an exhibit must be 255 characters or less.'));
        }

        if (strlen((string)$this->theme) > 30) {
            $this->addError('theme', __('The name of your theme must be 30 characters or less.'));
        }
    }

    /**
     * Delete callback.
     *
     * Delete all assigned pages when the exhibit is deleted.
     */
    protected function _delete()
    {
        //get all the pages and delete them
        $pages = $this->getTable('ExhibitPage')->findBy(array('exhibit'=>$this->id));
        foreach($pages as $page) {
            $page->delete();
        }
        $this->deleteTaggings();
    }

    /**
     * After-save callback.
     *
     * Updates search text and page data for the exhibit.
     *
     * @param array $args
     */
    protected function afterSave($args)
    {
        if (!$this->public) {
            $this->setSearchTextPrivate();
        }
        $this->setSearchTextTitle($this->title);
        $this->addSearchText($this->title);
        $this->addSearchText($this->description);
        
        if ($args['post']) {
            //Add the tags after the form has been saved
            $post = $args['post'];
            $this->applyTagString($post['tags']);
            if (isset($post['pages-hidden'])) {
                parse_str($post['pages-hidden'], $pageData);
                $this->_savePages($pageData['page']);
            }

            if (isset($post['pages-delete-hidden'])) {
                $pagesToDelete = explode(',', $post['pages-delete-hidden']);
                foreach ($pagesToDelete as $id) {
                    $page = $this->getTable('ExhibitPage')->find($id);
                    if ($page) {
                        $page->delete();
                    }
                }
            }
        }
    }

    /**
     * Save the order and parent data for the existing pages.
     *
     * @param array Page parent data array
     */
    protected function _savePages($pageData)
    {
        $orders = array();
        $ordersByParent = array();
        foreach ($pageData as $pageId => $parentId) {
            if ($parentId == 'null') {
                $pageData[$pageId] = null;
            }
            
            if (!isset($ordersByParent[$parentId])) {
                $order = $ordersByParent[$parentId] = 0;
            } else {
                $order = ++$ordersByParent[$parentId];
            }
            
            $orders[$pageId] = $order;
        }

        $pages = $this->getPages();
        foreach ($pages as $page) {
            $id = $page->id;
            if (array_key_exists($id, $pageData)) {
                $page->parent_id = $pageData[$id];
                $page->order = $orders[$id];
                $page->save();
            }
        }
    }

    /**
     * Get all the pages for this Exhibit.
     *
     * @return Exhibit[]
     */
    public function getPages()
    {
        return $this->getTable('ExhibitPage')->findBy(array('exhibit' => $this->id, 'sort_field' => 'order'));
    }

    /**
     * Get all the pages for this exhibit with no parent (top-level pages).
     *
     * @return Exhibit[]
     */
    public function getTopPages()
    {
        if (!$this->exists()) {
            return array();
        }

        return $this->getTable('ExhibitPage')->findBy(array('exhibit'=>$this->id, 'topOnly'=>true, 'sort_field'=>'order'));
    }

    public function getTopPageBySlug($slug)
    {

    }

    public function getFirstTopPage()
    {

    }

    /**
     * Get the number of pages for the exhibit. Optionally, restrict the count
     * to only top-level pages.
     *
     * @param boolean $topOnly Whether to count only top pages
     * @return ExhibitPage[]
     */
    public function countPages($topOnly = false)
    {
        return $this->getTable('ExhibitPage')->count(array(
            'exhibit' => $this->id, 'topOnly' => $topOnly));
    }

    /**
     * Alias for countPages, for compatibility purposes.
     *
     * @deprecated
     * @see countPages()
     * @param boolean $topOnly Whether to count only top pages
     * @return ExhibitPage[]
     */
    public function getPagesCount($topOnly = false)
    {
        return $this->countPages($topOnly);
    }

    /**
     * Determine whether an exhibit uses a particular item on any of its pages.
     *
     * @param Item $item
     * @return boolean
     */
    public function hasItem(Item $item)
    {
        if (!$item->exists()) {
           throw new InvalidArgumentException("Item does not exist (is not persisted).");
        }
        if (!$this->exists()) {
           throw new RuntimeException("Cannot call hasItem() on a new (non-persisted) exhibit.");
        }
        return $this->getTable()->exhibitHasItem($this->id, $item->id);
    }

    /**
     * Set options and optionally the theme name.
     *
     * @param array $themeOptions
     * @param string|null $themeName
     */
    public function setThemeOptions($themeOptions, $themeName = null)
    {
        if ($themeName === null) {
            $themeName = $this->theme;
        }
        if ($themeName !== null && $themeName != '') {
            $themeOptionsArray = unserialize($this->theme_options);
            $themeOptionsArray[$themeName] = $themeOptions;
        }

        $this->theme_options = serialize($themeOptionsArray);
    }

    /**
     * Get the options for the exhibit's theme.
     *
     * @param string|null $themeName If specified, get options for this theme
     *  instead of the exhibit's theme
     * @return array
     */
    public function getThemeOptions($themeName = null)
    {
        if ($themeName === null) {
            $themeName = $this->theme;
        }

        $themeName = (string)$themeName;
        if ($themeName == '' || empty($this->theme_options)) {
            return array();
        }

        $themeOptionsArray = unserialize($this->theme_options);
        return @$themeOptionsArray[$themeName];
    }

    /**
     * Get a URL to this exhibit with the specified action.
     *
     * @param string $action Action to link to
     * @return string
     */
    public function getRecordUrl($action = 'show')
    {
        if ('show' == $action) {
            return exhibit_builder_exhibit_uri($this);
        }

        $urlHelper = new Omeka_View_Helper_Url;
        $params = array('action' => $action, 'id' => $this->id);
        return $urlHelper->url($params, 'exhibitStandard');
    }

    /**
     * Get a representative file for this Exhibit.
     *
     * The representative is the first attached file in the exhibit.
     *
     * @return File|null
     */
    public function getFile()
    {
        $db = $this->getDb();
        $fileTable = $this->getDb()->getTable('File');
        $select =
            $fileTable->getSelect()
            ->joinInner(
                array('eba' => $db->ExhibitBlockAttachment),
                'eba.file_id = files.id',
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
            ->where('ep.exhibit_id = ?', $this->id)
            ->where('files.has_derivative_image = 1')
            ->order(array('ep.order', 'ep.parent_id', 'epb.order', 'eba.order'))
            ->limit(1);

        return $fileTable->fetchObject($select);
    }

    /**
     * Required by Zend_Acl_Resource_Interface.
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'ExhibitBuilder_Exhibits';
    }
}
