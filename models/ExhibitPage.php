<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPage model.
 *
 * @package ExhibitBuilder
 */
class ExhibitPage extends Omeka_Record_AbstractRecord
{
    /**
     * ID of parent page, if any
     *
     * @var integer
     */
    public $parent_id;

    /**
     * ID of the exhibit this page is in
     *
     * @var integer
     */
    public $exhibit_id;

    /**
     * URL slug for this page
     *
     * @var string
     */
    public $slug;

    /**
     * Title for the page
     *
     * @var string
     */
    public $title;

    /**
     * Order of the page underneath its parent/exhibit
     *
     * @var integer
     */
    public $order;

    /**
     * Related record linkages.
     *
     * @var array
     */
    protected $_related = array('ExhibitPageBlocks' => 'getPageBlocks');

    /**
     * Define mixins.
     *
     * @see Mixin_Slug
     * @see Mixin_Search
     */
    public function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Slug($this, array(
            'parentFields' => array('exhibit_id', 'parent_id'),
            'slugEmptyErrorMessage' => __('A slug must be given for each page of an exhibit.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('This page slug has already been used.  Please modify the slug so that it is unique.')));
        $this->_mixins[] = new Mixin_Search($this);
    }

    /**
     * In order to validate an exhibit must have a title.
     */
    protected function _validate()
    {
        if (!strlen($this->title)) {
            $this->addError('title', __('Exhibit pages must be given a title.'));
        }
    }

    /**
     * After save callback.
     *
     * Update block data and search data after saving.
     *
     * @var array $args
     */
    protected function afterSave($args)
    {
        if ($args['post']) {
            $post = $args['post'];

            if (!empty($post['blocks'])) {
                $this->setPageBlocks($post['blocks']);
            } else {
                $this->setPageBlocks(array());
            }
        }

        foreach ($this->getPageBlocks() as $block) {
            $this->addSearchText($block->text);
            foreach ($block->getAttachments() as $attachment) {
                $this->addSearchText($attachment->caption);
            }
        }

        $exhibit = $this->getExhibit();
        if (!$exhibit->public) {
            $this->setSearchTextPrivate();
        }
        $this->setSearchTextTitle($this->title);
        $this->addSearchText($this->title);
    }

    /**
     * Get the previous page.
     *
     * @return ExhibitPage
     */
    public function previous()
    {
        return $this->getDb()->getTable('ExhibitPage')->findPrevious($this);
    }

    /**
     * Get the next page.
     *
     * @return ExhibitPage
     */
    public function next()
    {
        return $this->getDb()->getTable('ExhibitPage')->findNext($this);
    }

    /**
     * Get the next page, preferring to step down to this page's children first.
     *
     * @return ExhibitPage
     */
    public function firstChildOrNext()
    {
        if($firstChild = $this->getFirstChildPage()) {
            return $firstChild;
        } else {
            //see if there's a next page on the same level
            $next = $this->next();
            if($next) {
                return $next;
            }
            // no next on same level, so bump up one level and go to next page
            // keep going up until we hit the top
            $current = $this;
            while (($current = $current->getParent())) {
                if (($parentNext = $current->next())) {
                    return $parentNext;
                }
            }
        }
    }

    /**
     * Get the previous page, or this page's parent if there are none.
     * 
     * @return ExhibitPage
     */
    public function previousOrParent()
    {
        $previous = $this->previous();
        if($previous) {
            while (($lastChild = $previous->getLastChildPage())) {
                $previous = $lastChild;
            }
            return $previous;
        } else {
            $parent = $this->getParent();
            if($parent) {
                return $parent;
            }
        }
    }

    /**
     * Get this page's parent.
     *
     * @return ExhibitPage
     */
    public function getParent()
    {
        return $this->getTable()->find($this->parent_id);
    }

    /**
     * Get all this page's children.
     *
     * @return ExhibitPage[]
     */
    public function getChildPages()
    {
        return $this->getTable()->findBy(array('parent'=>$this->id, 'sort_field'=>'order'));
    }

    /**
     * Get this page's first child.
     *
     * @return ExhibitPage
     */
    public function getFirstChildPage()
    {
        return $this->getTable()->findEndChild($this, 'first');
    }

    /**
     * Get this page's last child.
     *
     * @return ExhibitPage
     */
    public function getLastChildPage()
    {
        return $this->getTable()->findEndChild($this, 'last');
    }

    /**
     * Count the number of child pages for this page.
     *
     * @return integer
     */
    public function countChildPages()
    {
        return $this->getTable()->count(array('parent'=>$this->id));
    }

    /**
     * Get the ancestors of this page.
     *
     * @return ExhibitPage[]
     */
    public function getAncestors()
    {
        $ancestors = array();
        $page = $this;
        while ($page->parent_id) {
            $page = $page->getParent();
            $ancestors[] = $page;
        }
        $ancestors = array_reverse($ancestors);
        return $ancestors;

    }

    /**
     * Get this page's owning exhibit.
     *
     * @return Exhibit
     */
    public function getExhibit()
    {
        return $this->getTable('Exhibit')->find($this->exhibit_id);
    }

    /**
     * Delete owned blocks when deleting the page.
     *
     * Also, move childen of the page up a level in the hierarchy.
     */
    protected function _delete()
    {
        if ($this->ExhibitPageBlocks) {
            foreach ($this->ExhibitPageBlocks as $block) {
                $block->delete();
            }
        }

        //bump all child pages up to being children of the parent
        $childPages = $this->getChildPages();
        foreach($childPages as $child) {
            if($this->parent_id) {
                $child->parent_id = $this->parent_id;
            } else {
                $child->parent_id = NULL;
            }
            $child->save();
        }
    }

    /**
     * Get all blocks for this page.
     *
     * @return ExhibitPageBlock[]
     */
    public function getPageBlocks()
    {
        return $this->getTable('ExhibitPageBlock')->findByPage($this);
    }

    /**
     * Get all attachments for all this page's blocks.
     *
     * @return ExhibitBlockAttachment[]
     */
    public function getAllAttachments()
    {
        return $this->getTable('ExhibitBlockAttachment')->findByPage($this);
    }

    /**
     * Set data for this page's blocks.
     *
     * @param array $blocksData An array of key-value arrays for each block.
     * @param boolean $deleteExtras Whether to delete any extra preexisting
     *  blocks.
     */ 
    public function setPageBlocks($blocksData, $deleteExtras = true)
    {
        $existingBlocks = $this->getPageBlocks();
        foreach ($blocksData as $i => $blockData) {
            if (!empty($existingBlocks)) {
                $block = array_pop($existingBlocks);
            } else {
                $block = new ExhibitPageBlock;
                $block->page_id = $this->id;
            }
            $block->order = $i;
            $block->setData($blockData);
            $block->save();
        }
        // Any leftover blocks beyond the new data get erased.
        if ($deleteExtras) {
            foreach ($existingBlocks as $extraBlock) {
                $extraBlock->delete();
            }
        }
    }

    /**
     * Get the URL to this page, with the specified action.
     *
     * @param string $action The action to link to
     * @return string
     */
    public function getRecordUrl($action = 'show')
    {
        if ('show' == $action) {
            return exhibit_builder_exhibit_uri($this->getExhibit(), $this);
        }
        return array('module' => 'exhibit-builder', 'controller' => 'exhibits', 
                     'action' => $action, 'id' => $this->id);
    }
}
