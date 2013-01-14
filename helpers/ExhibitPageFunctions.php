<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

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
 * Return the text of the given exhibit page entry.
 *
 * @param int $entryIndex Page entry index, defaults to 1
 * @param ExhibitPage|null $exhibitPage If null, uses the current exhibit page
 * @return string
 */
function exhibit_builder_page_text($entryIndex = 1, $exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }

    if (count($exhibitPage->ExhibitPageEntry) < $entryIndex) {
        $text = '';
    } else {
        $text = $exhibitPage->ExhibitPageEntry[(int) $entryIndex]->text;
    }

    return $text;
}

/**
 * Return the data for an attached item/file.
 *
 * @param int $entryIndex Page entry index, defaults to 1
 * @param int $fallbackFileIndex File index to choose if no file was picked
 *  specifically. Defaults to 0, the first file for the item
 * @param ExhibitPage|null Page to use, if null, the current page is used
 * @return array|null Null if no such entry exists. If one does, returns an
 *  array, with the following keys:
 *   * item: the attached Item object
 *   * file: the File to be displayed (null if no file exists)
 *   * file_specified: boolean, whether the file was user-selected or auto-picked
 *   * caption: a string, the attachment's caption
 */
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

    $item = null;
    $file = null;
    $file_specified = false;
    $caption = null;

    if (($item = $entry->Item)) { 
        if (($file = $entry->File)) {
            $file_specified = true;
        } else if (isset($item->Files[$fallbackFileIndex])) {
            $file = $item->Files[$fallbackFileIndex];
        }
    } else {
        // If there's no item, nothing is attached.
        return null;
    }

    $caption = $entry->caption;

    return compact(array('item', 'file', 'file_specified', 'caption'));
}

/**
 * Register the item/file from an attachment as the current records to work on.
 *
 * @see exhibit_builder_page_attachment
 * @param array $attachment
 */
function exhibit_builder_use_attachment($attachment)
{
    set_current_record('item', $attachment['item']);
    set_current_record('file', $attachment['file']);
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
 * Return a link to the next exhibit page
 *
 * @param string $text Link text
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 */
function exhibit_builder_link_to_next_page($text = null, $props = array(), $exhibitPage = null)
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
 * Return a link to the previous exhibit page
 *
 * @param string $text Link text
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 */
function exhibit_builder_link_to_previous_page($text = null, $props = array(), $exhibitPage = null)
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
 * Return a link to the parent exhibit page
 *
 * @param string $text Link text
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page
 * @return string
 */
function exhibit_builder_link_to_parent_page($text = null, $props = array(), $exhibitPage = null)
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
 * Loop through and render all of a page's child pages
 *
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
 * Render a page's summary info according to the page-summary.php template
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
