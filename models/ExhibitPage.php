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
    public $id;
    public $parent_id;
    public $exhibit_id;
    public $slug;
    public $title;
    public $order;

    protected $_related = array('ExhibitPageBlock' => 'getPageBlocks');

    public function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Order($this, 'ExhibitPageBlock', 'page_id');
        $this->_mixins[] = new Mixin_Slug($this, array(
            'parentFields' => array('exhibit_id', 'parent_id'),
            'slugEmptyErrorMessage' => __('A slug must be given for each page of an exhibit.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('This page slug has already been used.  Please modify the slug so that it is unique.')));
        $this->_mixins[] = new Mixin_Search($this);
    }

    /**
     * In order to validate:
     * 1) must have a layout
     * 2) Must have a title
     * 3) must be properly ordered
     * 
     * @return void
     */
    protected function _validate()
    {
        if (!strlen($this->title)) {
            $this->addError('title', __('Exhibit pages must be given a title.'));
        }
    }
    
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
            foreach ($block->getAttachments() as $attachment) {
                $this->addSearchText($attachment->text);
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

    public function previous()
    {
        return $this->getDb()->getTable('ExhibitPage')->findPrevious($this);
    }

    public function next()
    {
        return $this->getDb()->getTable('ExhibitPage')->findNext($this);
    }

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
            //no next on same level, so bump up one level and go to next page
            $parent = $this->getParent();
            if($parent) {
                $parentNext = $parent->next();
                return $parentNext;
            }
        }
    }

    public function previousOrParent()
    {
        $previous = $this->previous();
        if($previous) {
            if($previousLastChildPage = $previous->getLastChildPage()) {
                return $previousLastChildPage;
            }
            return $previous;
        } else {
            $parent = $this->getParent();
            if($parent) {
                return $parent;
            }
        }
    }

    public function getParent()
    {
        return $this->getTable()->find($this->parent_id);
    }

    public function getChildPages()
    {
        return $this->getTable()->findBy(array('parent'=>$this->id, 'sort_field'=>'order'));
    }

    public function getFirstChildPage()
    {
        return $this->getTable()->findEndChild($this, 'first');
    }

    public function getLastChildPage()
    {
        return $this->getTable()->findEndChild($this, 'last');
    }

    public function countChildPages()
    {
        return $this->getTable()->count(array('parent'=>$this->id));
    }

    /**
     * Get the ancestors of the page
     *
     * @return array
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

    public function getExhibit()
    {
        return $this->getTable('Exhibit')->find($this->exhibit_id);
    }

    protected function _delete()
    {
        if ($this->ExhibitPageBlock) {
            foreach ($this->ExhibitPageBlock as $block) {
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

    public function getPageBlocks()
    {
        return $this->loadOrderedChildren();
    }

    public function getAllAttachments()
    {
        return $this->getTable('ExhibitBlockAttachment')->findAllForPage($this);
    }

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

    public function getRecordUrl($action = 'show')
    {
        if ('show' == $action) {
            return exhibit_builder_exhibit_uri($this->getExhibit(), $this);
        }
        return array('module' => 'exhibit-builder', 'controller' => 'exhibits', 
                     'action' => $action, 'id' => $this->id);
    }
}
