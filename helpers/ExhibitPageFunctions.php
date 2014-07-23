<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Render the markup for an exhibit page.
 *
 * @param ExhibitPage|null $exhibitPage
 */
function exhibit_builder_render_exhibit_page($exhibitPage = null)
{
    if ($exhibitPage === null) {
        $exhibitPage = get_current_record('exhibit_page');
    }
    
    $blocks = $exhibitPage->ExhibitPageBlocks;
    $rawAttachments = $exhibitPage->getAllAttachments();
    $attachments = array();
    foreach ($rawAttachments as $attachment) {
        $attachments[$attachment->block_id][] = $attachment;
    }
    foreach ($blocks as $index => $block) {
        $layout = $block->getLayout();
        echo '<div class="exhibit-block layout-' . html_escape($layout->id) . '">';
        echo get_view()->partial($layout->getViewPartial(), array(
            'index' => $index,
            'options' => $block->getOptions(),
            'text' => $block->text,
            'attachments' => array_key_exists($block->id, $attachments) ? $attachments[$block->id] : array()
        ));
        echo '</div>';
    }
}

/**
 * Return whether an exhibit page is the current exhibit page.
 *
 * @param ExhibitPage|null $exhibitPage
 * @return boolean
 */
function exhibit_builder_is_current_page($exhibitPage)
{
    $currentExhibitPage = get_current_record('exhibit_page', false);
    return ($exhibitPage === $currentExhibitPage
        || ($exhibitPage && $currentExhibitPage && $exhibitPage->id == $currentExhibitPage->id));
}

/**
 * Return the markup for the exhibit page navigation.
 *
 * @param ExhibitPage|null $exhibitPage If null, uses the current exhibit page
 * @return string
 */
function exhibit_builder_page_nav($exhibitPage = null)
{
    if (!$exhibitPage) {
        if (!($exhibitPage = get_current_record('exhibit_page', false))) {
            return;
        }
    }

    $exhibit = $exhibitPage->getExhibit();
    $html = '<ul class="exhibit-page-nav navigation" id="secondary-nav">' . "\n";
    $pagesTrail = $exhibitPage->getAncestors();
    $pagesTrail[] = $exhibitPage;
    $html .= '<li>';
    $html .= '<a class="exhibit-title" href="'. html_escape(exhibit_builder_exhibit_uri($exhibit)) . '">';
    $html .= html_escape($exhibit->title) .'</a></li>' . "\n";
    foreach ($pagesTrail as $page) {
        $linkText = $page->title;
        $pageExhibit = $page->getExhibit();
        $pageParent = $page->getParent();
        $pageSiblings = ($pageParent ? exhibit_builder_child_pages($pageParent) : $pageExhibit->getTopPages()); 

        $html .= "<li>\n<ul>\n";
        foreach ($pageSiblings as $pageSibling) {
            $html .= '<li' . ($pageSibling->id == $page->id ? ' class="current"' : '') . '>';
            $html .= '<a class="exhibit-page-title" href="' . html_escape(exhibit_builder_exhibit_uri($exhibit, $pageSibling)) . '">';
            $html .= html_escape($pageSibling->title) . "</a></li>\n";
        }
        $html .= "</ul>\n</li>\n";
    }
    $html .= '</ul>' . "\n";
    $html = apply_filters('exhibit_builder_page_nav', $html);
    return $html;
}

/**
 * Return the markup for the exhibit child page navigation.
 *
 * @param ExhibitPage|null $exhibitPage If null, uses the current exhibit page
 * @return string
 */
function exhibit_builder_child_page_nav($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    $exhibit = $exhibitPage->getExhibit();
    $children = exhibit_builder_child_pages($exhibitPage);
    $html = '<ul class="exhibit-child-nav navigation">' . "\n";
    foreach ($children as $child) {
        $html .= '<li><a href="' . html_escape(exhibit_builder_exhibit_uri($exhibit, $child)) . '">' . html_escape($child->title) . '</a></li>';
    }
    $html .= '</ul>' . "\n";
    return $html;
}

/**
 * Return a link to the next exhibit page
 *
 * @param string $text Link text
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 */
function exhibit_builder_link_to_next_page($text = null, $props = array(), $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

    $targetPage = null;

    // if page object exists, grab link to the first child page if exists. If it doesn't, grab
    // a link to the next page
    if ($targetPage = $exhibitPage->firstChildOrNext()) {
        if (!isset($props['class'])) {
            $props['class'] = 'next-page';
        }
        if ($text === null) {
            $text = metadata($targetPage, 'title') . ' &rarr;';
        }
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $targetPage);
    }

    return null;
}

/**
 * Return a link to the previous exhibit page
 *
 * @param string $text Link text
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 */
function exhibit_builder_link_to_previous_page($text = null, $props = array(), $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }
    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

    // If page object exists, grab link to previous exhibit page if exists. If it doesn't, grab
    // a link to the last page on the previous parent page, or the exhibit if at top level
    if ($previousPage = $exhibitPage->previousOrParent()) {
        if(!isset($props['class'])) {
            $props['class'] = 'previous-page';
        }
        if ($text === null) {
            $text = '&larr; ' . metadata($previousPage, 'title');
        }
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $previousPage);
    }

    return null;
}

/**
 * Return a trail of parent pages, ending in the current page's name.
 *
 * @param ExhibitPage|null $exhibitPage The page to print the trail to.
 * @return string
 */
function exhibit_builder_page_trail($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }
    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

    $currentPage = $exhibitPage;
    $parents = array();
    while ($currentPage->parent_id) {
        $currentPage = $currentPage->getParent();
        array_unshift($parents, $currentPage);
    }

    $html = '';
    foreach ($parents as $parent) {
        $text = metadata($parent, 'title');
        $html .= exhibit_builder_link_to_exhibit($exhibit, $text, array(), $parent);
        $html .= '<br>';
        release_object($parent);
    }

    $html .= '<span class="current-page">' . metadata($exhibitPage, 'title') . '</span>';
    return $html;
}

/**
 * Return a link to the parent exhibit page
 *
 * @param string $text Link text
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 */
function exhibit_builder_link_to_parent_page($text = null, $props = array(), $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }
    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

    if($exhibitPage->parent_id) {
        $parentPage = $exhibitPage->getParent();
        if(!isset($props['class'])) {
            $props['class'] = 'parent-page';
        }
        if ($text === null) {
            $text = '&uarr; ' . metadata($parentPage, 'title');
        }
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $parentPage);
    }

    return null;
}

/**
 * Set a page's children as the pages for looping.
 *
 * @param ExhibitPage|null $exhibitPage If null, uses the current page
 */
function set_exhibit_pages_for_loop_by_parent_page($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    set_loop_records('exhibit_page', $exhibitPage->getChildPages());
}

/**
 * Set a exhibit's pages as the pages for looping.
 *
 * @param Exhibit|null If null, uses the current exhibit
 */
function set_exhibit_pages_for_loop_by_exhibit($exhibit = null)
{
    if(!$exhibit) {
        $exhibit = get_current_record('exhibit');
    }

    set_loop_records('exhibit_page', $exhibit->TopPages);
}

/**
 * Get the children of a page.
 * 
 * @param ExhibitPage $exhibitPage The exhibit page. If null, uses the current page.
 * @return array[ExhibitPage]
 */
function exhibit_builder_child_pages($exhibitPage = null)
{
    if(!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    return $exhibitPage->getChildPages();
}

/**
 * Get a list item for a page, containing a sublist of all its children.
 */
function exhibit_builder_page_summary($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    $html = '<li>'
          . '<a href="' . exhibit_builder_exhibit_uri(get_current_record('exhibit'), $exhibitPage) . '">'
          . metadata($exhibitPage, 'title') .'</a>';

    $children = $exhibitPage->getChildPages();
    if ($children) {
        $html .= '<ul>';
        foreach ($children as $child) {
            $html .= exhibit_builder_page_summary($child);
            release_object($child);
        }
        $html .= '</ul>';
    }
    $html .= '</li>';
    return $html;
}
