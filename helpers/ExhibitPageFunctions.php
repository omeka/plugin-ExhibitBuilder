<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Returns whether an exhibit page is the current exhibit page.
 *
 * @param ExhibitPage|null $exhibitPage
 * @return boolean
 **/
function exhibit_builder_is_current_page($exhibitPage)
{
    $currentExhibitPage = get_current_record('exhibit_page', false);
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
        $exhibitPage = get_current_record('exhibit_page');
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
        $exhibitPage = get_current_record('exhibit_page', false);
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
        $exhibitPage = get_current_record('exhibit_page');
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

function exhibit_builder_page_attachment($entryIndex = 1, $fallbackFileIndex = 0, $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    $entries = $exhibitPage->ExhibitPageEntry;

    if (!isset($entries[$entryIndex])) {
        return null;
    }
    
    $entry = $entries[$entryIndex];
    $attachment = array();
    if ($item = $entry->Item) {
        $attachment['item'] = $item; 
        
        if ($file = $entry->File) {
            $attachment['file'] = $file;
        } else if (isset($item->Files[$fallbackFileIndex])) {
            $attachment['file'] = $item->Files[$fallbackFileIndex];
        }
    }

    if ($caption = $entry->caption) {
        $attachment['caption'] = $caption;
    }

    return $attachment;    
}

function exhibit_builder_use_attachment($attachment)
{
    if (isset($attachment['item'])) {
        set_current_record('item', $attachment['item']);
    }

    if (isset($attachment['file'])) {
        set_current_record('file', $attachment['file']);
    }
}

/**
 * Returns the HTML code of the exhibit page navigation
 *
 * @param ExhibitPage|null $exhibitPage If null, will use the current exhibit page
 * @return string
 **/
function exhibit_builder_page_nav($exhibitPage = null)
{
    if (!$exhibitPage) {
        if (!($exhibitPage = get_current_record('exhibit_page', false))) {
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
        $linkText = $page->title;
        $html .= '<li'. (exhibit_builder_is_current_page($page) ? ' class="current"' : '').'>';
        $html .= '<a class="exhibit-page-title" href="'. html_escape(exhibit_builder_exhibit_uri($exhibit, $page)) . '">';
        $html .= html_escape($linkText) .'</a></li>' . "\n";
    }
    $html .= '</ul>' . "\n";
    $html = apply_filters('exhibit_builder_page_nav', $html);
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
        $exhibitPage = get_current_record('exhibit_page');
    }

    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

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
        $exhibitPage = get_current_record('exhibit_page');
    }
    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

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
        $exhibitPage = get_current_record('exhibit_page');
    }
    $exhibit = get_record_by_id('Exhibit', $exhibitPage->exhibit_id);

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
 * Sets the exhibit child pages for loop by their parent
 *
 * @param ExhibitPage|null $exhibitPage If null, it uses the current page
 * @return void
 **/
function set_exhibit_pages_for_loop_by_parent_page($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    set_loop_records('exhibit_page', $exhibitPage->getChildPages());
}

function set_exhibit_pages_for_loop_by_exhibit($exhibit = null)
{
    if(!$exhibit) {
        $exhibit = get_current_record('exhibit');
    }

    set_loop_records('exhibit_page', $exhibit->TopPages);
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
        $exhibitPage = get_current_record('exhibit_page');
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
        $exhibitPage = get_current_record('exhibit_page');
    }
    set_current_record('exhibit_page', $exhibitPage);
    include(EXHIBIT_PLUGIN_DIR . '/views/public/exhibits/page-summary.php');
}


/**
 * Generate a URL slug from a piece of text.
 *
 * Trims whitespace, replaces disallowed characters with hyphens,
 * converts the resulting string to lowercase, and trims at 30 characters.
 *
 * @param string $text
 * @return string
 */
function exhibit_builder_generate_slug($text)
{
    // Remove characters other than alphanumeric, hyphen, underscore.
    $slug = preg_replace('/[^a-z0-9\-_]/', '-', strtolower(trim($text)));
    // Trim down to 30 characters.
    return substr($slug, 0, 30);
}
