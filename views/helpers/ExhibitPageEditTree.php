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
              . '<div class="sortable-item">'
              . '<a href="../edit-page/' . $id . '">' . $title . '</a>'
              . '<a class="delete-toggle delete-element" href="#">' . __('Delete') . '</a>'
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
