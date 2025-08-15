<?php

require_once dirname(__FILE__) . '/ExhibitPageTree.php';

/**
 * View helper for a unordered list "tree" of pages in an exhibit.
 *
 * This version is modified for the markup used on the edit page.
 * 
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitPageEditTree
    extends ExhibitBuilder_View_Helper_ExhibitPageTree
{

    /**
     * Entry point for the helper.
     *
     * @param Exhibit $exhibit
     * @return string
     */
    public function exhibitPageEditTree($exhibit)
    {
        return $this->exhibitPageTree($exhibit);
    }

    /**
     * Get the opening tag of the outermost list.
     *
     * @return string
     */
    protected function _renderListOpening()
    {
        return '<ul id="page-list" class="sortable">';
    }

    /**
     * Recursively create the HTML for a "branch" (a page and its descendants)
     * of the tree.
     *
     * @param ExhibitPage $page
     * @param ExhibitPage|null $currentPage
     * @param array $ancestorIds
     * @return string
     */
    protected function _renderPageBranch($page, $currentPage, $ancestorIds)
    {
        $id = html_escape($page->id);
        $title = html_escape($page->title);

        $html = '<li class="page" id="page_' . $id . '">'
            . '<div class="sortable-item drawer">'
            . '<span id="move-' . $id . '" class="move icon" title="' . __('Move') . '" aria-label="' . __('Move') . '" aria-labelledby="move-' . $id .  'element-' . $id . '"></span>'
            . '<a href="../edit-page/' . $id . '" class="drawer-name">' . $title . '</a>'
            . '<button class="undo-delete" data-action-selector="deleted" type="button" aria-label="' . __('Undo remove') . '" title="' . __('Undo remove') . '"><span class="icon"></span></button>'
            . '<button class="delete-drawer" data-action-selector="deleted" type="button" aria-label="' . __('Remove') . '" title="' . __('Remove') . '"><span class="icon"></span></button>'
            . '</div>';

        if (isset($this->_pages[$page->id])) {
            $html .= '<ul>';
            foreach ($this->_pages[$page->id] as $childPage) {
                $html .= $this->_renderPageBranch($childPage, $currentPage, $ancestorIds);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        return $html;
    }
}
