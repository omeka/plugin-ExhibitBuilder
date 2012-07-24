<?php

/**
 * Returns the current page.
 *
 * @return ExhibitPage|null
 **/
function exhibit_builder_get_current_page()
{
    return __v()->exhibitPage;
}

/**
 * Sets the current exhibit page.
 *
 * @param ExhibitPage|null $exhibitPage
 * @return void
 **/
function exhibit_builder_set_current_page($exhibitPage = null)
{
    __v()->exhibitPage = $exhibitPage;
}

/**
 * Returns whether an exhibit page is the current exhibit page.
 *
 * @param ExhibitPage|null $exhibitPage
 * @return boolean
 **/
function exhibit_builder_is_current_page($exhibitPage)
{
    $currentExhibitPage = exhibit_builder_get_current_page();
    return ($exhibitPage === $currentExhibitPage || ($exhibitPage && $currentExhibitPage && $exhibitPage->id == $currentExhibitPage->id));
}

/**
 * Returns the text of the exhibit page entry
 *
 * @param int $exhibitPageEntryIndex The i-th page entry, where i = 1, 2, 3, ...
 * @param ExhibitPage|null $exhibitPage If null, it will use the current exhibit page
 * @return string
 **/
function exhibit_builder_page_text($exhibitPageEntryIndex = 1, $exhibitPage=null)
{
    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }

    if (count($exhibitPage->ExhibitPageEntry) < $exhibitPageEntryIndex) {
        $text = '';
    } else {
        $text = $exhibitPage->ExhibitPageEntry[(int) $exhibitPageEntryIndex]->text;
    }

    return $text;
}

/**
 * Returns the caption of an exhibit page entry
 *
 * @param int $exhibitPageEntryIndex The i-th page entry, where i = 1, 2, 3, ...
 * @param ExhibitPage|null $exhibitPage If null, it will use the current exhibit page
 * @return string
 **/
function exhibit_builder_page_caption($exhibitPageEntryIndex = 1, $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }

    if (!$exhibitPage || count($exhibitPage->ExhibitPageEntry) < $exhibitPageEntryIndex) {
        $caption = '';
    } else {
        $caption = $exhibitPage->ExhibitPageEntry[(int) $exhibitPageEntryIndex]->caption;
    }

    return $caption;
}

/**
 * Returns an item of an exhibit page entry
 *
 * @param int $exhibitPageEntryIndex The i-th page entry, where i = 1, 2, 3, ...
 * @param ExhibitPage|null $exhibitPage If null, will use the current exhibit page
 * @return Item
 **/
function exhibit_builder_page_item($exhibitPageEntryIndex = 1, $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }

    if (count($exhibitPage->ExhibitPageEntry) < $exhibitPageEntryIndex) {
        $item = null;
    } else {
        $item = $exhibitPage->ExhibitPageEntry[(int) $exhibitPageEntryIndex]->Item;
        if (!$item || !$item->exists()) {
            $item = null;
        }
    }

    return $item;
}

/**
 * Returns the HTML code of the exhibit page navigation
 *
 * @param ExhibitPage|null $exhibitPage If null, will use the current exhibit page
 * @param string $linkTextType The type of page information should be used for the link text.
 * If 'order', it uses the page order as the link text.
 * If 'title' or any other value, it uses the page title as the link text.
 * @return string
 **/
function exhibit_builder_page_nav($exhibitPage = null, $linkTextType = 'title')
{
    $linkTextType = Inflector::underscore($linkTextType);
    if (!$exhibitPage) {
        if (!($exhibitPage = exhibit_builder_get_current_page())) {
            return;
        }
    }

    $exhibit = get_db()->getTable('Exhibit')->find($exhibitPage->exhibit_id);
    $html = '<ul class="exhibit-page-nav">' . "\n";
    $pagesTrail = $exhibitPage->getAncestors();
    $pagesTrail[] = $exhibitPage;
    $html .= '<li>';
    $html .= '<a class="exhibit-page-title" href="'. html_escape(exhibit_builder_exhibit_uri($exhibit)) . '">';
    $html .= html_escape($exhibit->title) .'</a></li>' . "\n";

    foreach ($pagesTrail as $page) {
        switch($linkTextType) {
            case 'order':
                $linkText = $page->order;
                break;
            case 'title':
            case 'Title':
            default:
                $linkText = $page->title;
                break;
        }
        $html .= '<li'. (exhibit_builder_is_current_page($page) ? ' class="current"' : '').'>';
        $html .= '<a class="exhibit-page-title" href="'. html_escape(exhibit_builder_exhibit_uri($exhibit, $page)) . '">';
        $html .= html_escape($linkText) .'</a></li>' . "\n";
    }
    $html .= '</ul>' . "\n";
    $html = apply_filters('exhibit_builder_page_nav', $html, $linkTextType);
    return $html;

}

/**
 * Returns a link to the next exhibit page
 *
 * @param string $text The label for the next page link
 * @param array $props
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 **/
function exhibit_builder_link_to_next_exhibit_page($text = null, $props = array(), $exhibitPage = null)
{
    if ($text === null) {
        $text = __('Next Page &rarr;');
    }

    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }

    $exhibit = exhibit_builder_get_exhibit_by_id($exhibitPage->exhibit_id);

    if(!isset($props['class'])) {
        $props['class'] = 'next-page';
    }

    // if page object exists, grab link to the first child page if exists. If it doesn't, grab
    // a link to the next page
    if ($nextPage = $exhibitPage->firstChildOrNext()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $nextPage);
    } elseif ($exhibitPage->parent_id) {
        $parentPage = $exhibitPage->getParent();
        $nextParentPage = $parentPage->next();
        if($nextParentPage) {
            return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $nextParentPage);
        }

    }
}

/**
 * Returns a link to the previous exhibit page
 *
 * @param string $text The label for the previous page link
 * @param array $props
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 **/
function exhibit_builder_link_to_previous_exhibit_page($text = null, $props = array(), $exhibitPage = null)
{
    if ($text === null) {
        $text = __('&larr; Previous Page');
    }

    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }
    $exhibit = exhibit_builder_get_exhibit_by_id($exhibitPage->exhibit_id);

    if(!isset($props['class'])) {
        $props['class'] = 'previous-page';
    }

    // If page object exists, grab link to previous exhibit page if exists. If it doesn't, grab
    // a link to the last page on the previous parent page, or the exhibit if at top level
    if ($previousPage = $exhibitPage->previousOrParent()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $previousPage);
    }
}

/**
 * Returns a link to the parent exhibit page
 *
 * @param string $text The label for the previous page link
 * @param array $props
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 *
 */
function exhibit_builder_link_to_parent_exhibit_page($text = null, $props = array(), $exhibitPage = null)
{
    if ($text === null) {
        $text = __('&uarr; Up');
    }

    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }
    $exhibit = exhibit_builder_get_exhibit_by_id($exhibitPage->exhibit_id);

    if(!isset($props['class'])) {
        $props['class'] = 'parent-page';
    }

    if($exhibitPage->parent_id) {
        $parentPage = $exhibitPage->getParent();
        $link = exhibit_builder_link_to_exhibit($exhibit, $text, $props, $parentPage);
        return $link;
    } else {
        return '';
    }


}


/**
 * Returns whether an exhibit page has an item
 *
 * @todo Needs optimization (shouldn't return the item object every time it's checked).
 * @param int $exhibitPageEntryIndex The i-th page entry, where i = 1, 2, 3, ...
 * @param ExhibitPage|null $exhibitPage If null, will use the current exhibit page
 * @return boolean
 **/
function exhibit_builder_exhibit_page_has_item($exhibitPageEntryIndex = 1, $exhibitPage = null)
{
    return (boolean)exhibit_builder_page_item($exhibitPageEntryIndex, $exhibitPage);
}

/**
 * Returns an item at the specified page entry index of an exhibit page.
 * If no item exists on the page, it returns false.
 *
 * @param int $exhibitPageEntryIndex The i-th page entry, where i = 1, 2, 3, ...
 * @return Item|boolean
 **/
function exhibit_builder_use_exhibit_page_item($exhibitPageEntryIndex = 1)
{
    $item = exhibit_builder_page_item($exhibitPageEntryIndex);
    if ($item instanceof Item) {
        set_current_item($item);
        return $item;
    }
    return false;
}

/**
* Gets the current exhibit page
*
* @return ExhibitPage|null
**/
function get_current_exhibit_page()
{
    return exhibit_builder_get_current_page();
}

/**
 * Sets the current exhibit page
 *
 * @see loop_exhibit_pages()
 * @param ExhibitPage
 * @return void
 **/
function set_current_exhibit_page(ExhibitPage $exhibitPage)
{
   exhibit_builder_set_current_page($exhibitPage);
}

/**
 * Sets the exhibit pages for loop
 *
 * @param array $exhibitPages
 * @return void
 **/
function set_exhibit_pages_for_loop($exhibitPages)
{
    __v()->exhibitPages = $exhibitPages;
}

/**
 * Sets the exhibit child pages for loop by their parent
 *
 * @param ExhibitPage|null $exhibitPage If null, it uses the current page
 * @return void
 **/
function set_exhibit_pages_for_loop_by_parent_page($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }

    set_exhibit_pages_for_loop($exhibitPage->getChildPages());
}

function set_exhibit_pages_for_loop_by_exhibit($exhibit = null)
{
    if(!$exhibit) {
        $exhibit = get_current_exhibit();
    }

    set_exhibit_pages_for_loop($exhibit->TopPages);
}

/**
 * Get the set of exhibit pages for the current loop.
 *
 * @return array
 **/
function get_exhibit_pages_for_loop()
{
    return __v()->exhibitPages;
}

/**
 * Loops through exhibit pages assigned to the view.
 *
 * @return mixed The current exhibit page
 */
function loop_exhibit_pages()
{
    return loop_records('exhibitPages', get_exhibit_pages_for_loop(), 'set_current_exhibit_page');
}

/**
 * Determine whether or not there are any exhibit pages in the database.
 *
 * @return boolean
 **/
function has_exhibit_pages()
{
    return (total_exhibit_pages() > 0);
}

/**
 * Determines whether there are any exhibit pages for loop.
 * @return boolean
 */
function has_exhibit_pages_for_loop()
{
    $view = __v();
    return ($view->exhibitPages and count($view->exhibitPages));
}

/**
  * Returns the total number of exhibit pages in the database
  *
  * @return integer
  **/
 function total_exhibit_pages()
 {
     return get_db()->getTable('ExhibitPage')->count();
 }

/**
* Gets a property from an exhibit page
*
* @param string $propertyName
* @param array $options
* @param Exhibit $exhibitPage  The exhibit page
* @return mixed The exhibit page property value
**/
function exhibit_page($propertyName, $options = array(), $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_exhibit_page();
    }
    $propertyName = Inflector::underscore($propertyName);
    if (property_exists(get_class($exhibitPage), $propertyName)) {
        return html_escape($exhibitPage->$propertyName);
    } else {
        return null;
    }
}

/**
 * Return a page's child pages
 * @since 2.0
 * @param ExhibitPage $exhibitPage The exhibit page. Null gets the current exhibit page
 * @return array
 *
 */

function exhibit_builder_child_pages($exhibitPage = null)
{
    if(!$exhibitPage) {
        $exhibitPage = get_current_exhibit_page();
    }

    return $exhibitPage->getChildPages();
}

/**
 * Loops through and renders a page's child pages
 *
 * @since 2.0
 * @param ExhibitPage $exhibitPage The exhibit page. Null gets the current exhibit page
 */

function exhibit_builder_page_loop_children($exhibitPage = null)
{
    $childPages = exhibit_builder_child_pages($exhibitPage);

    foreach($childPages as $exhibitPage) {
        exhibit_builder_render_page_summary($exhibitPage);
    }
}

/**
 * Renders a page's summary info according to the page-summary.php template
 */
function exhibit_builder_render_page_summary($exhibitPage = null)
{
    if(!$exhibitPage) {
        $exhibitPage = get_current_exhibit_page();
    }
    set_current_exhibit_page($exhibitPage);
    include(EXHIBIT_PLUGIN_DIR . '/views/public/exhibits/page-summary.php');
}
