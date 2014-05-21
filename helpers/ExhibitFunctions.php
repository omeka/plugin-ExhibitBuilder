<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Recursively list the pages under a page for editing.
 *
 * @param ExhibitPage $page A page to list.
 * @return string
 */
function exhibit_builder_edit_page_list($page)
{
    $pageId = html_escape($page->id);
    $html = '<li class="page" id="page_' . $pageId . '">'
          . '<div class="sortable-item">'
          . '<a href="../edit-page/' . $pageId . '">' . html_escape($page->title) . '</a>'
          . '<a class="delete-toggle delete-element" href="#">' . __('Delete') . '</a>'
          . '</div>';

    if (($children = $page->getChildPages())) {
        $html .= '<ul>';
        foreach ($children as $child) {
            $html .= exhibit_builder_edit_page_list($child);
        }
        $html .= '</ul>';
    }
    $html .= '</li>';
    return $html;
}

/**
 * Return whether an exhibit is the current exhibit.
 *
 * @param Exhibit $exhibit
 * @return boolean
 */
function exhibit_builder_is_current_exhibit($exhibit)
{
    $currentExhibit = get_current_record('exhibit', false);
    return ($exhibit == $currentExhibit
        || ($exhibit && $currentExhibit && $exhibit->id == $currentExhibit->id));
}

/**
 * Return a link to an exhibit.
 *
 * @param Exhibit $exhibit If null, it uses the current exhibit
 * @param string $text The text of the link
 * @param array $props Link attributes
 * @param ExhibitPage $exhibitPage A specific page to link to
 * @return string
 */
function exhibit_builder_link_to_exhibit($exhibit = null, $text = null, $props = array(), $exhibitPage = null)
{
    if (!$exhibit) {
        $exhibit = get_current_record('exhibit');
    }
    $uri = exhibit_builder_exhibit_uri($exhibit, $exhibitPage);
    $text = !empty($text) ? $text : html_escape($exhibit->title);
    return '<a href="' . html_escape($uri) .'" '. tag_attributes($props) . '>' . $text . '</a>';
}

/**
 * Return a URI to an exhibit.
 *
 * @param Exhibit $exhibit If null, it uses the current exhibit.
 * @param ExhibitPage $exhibitPage A specific page to link to
 * @return string
 */
function exhibit_builder_exhibit_uri($exhibit = null, $exhibitPage = null)
{
    if (!$exhibit) {
        $exhibit = get_current_record('exhibit');
    }
    $exhibitSlug = ($exhibit instanceof Exhibit) ? $exhibit->slug : $exhibit;

    //If there is no page slug available, we want to build a URL for the summary page
    if (!$exhibitPage) {
        $uri = public_url(array('slug'=>$exhibitSlug), 'exhibitSimple');
    } else {
        $pagesTrail = $exhibitPage->getAncestors();
        $pagesTrail[] = $exhibitPage;
        $options = array();
        $options['slug'] = $exhibitSlug;
        foreach($pagesTrail as $index=>$page) {
            $adjustedIndex = $index + 1;
            $options["page_slug_$adjustedIndex"] = $page->slug;
        }

        $uri = public_url($options, 'exhibitShow', array(), true);
    }
    return $uri;
}

/**
 * Return a link to an item within an exhibit.
 *
 * @param string $text Link text (by default, the item title is used)
 * @param array $props Link attributes
 * @param Item $item If null, will use the current item.
 * @return string
 */
function exhibit_builder_link_to_exhibit_item($text = null, $props = array(), $item = null)
{
    if (!$item) {
        $item = get_current_record('item');
    }

    if (!isset($props['class'])) {
        $props['class'] = 'exhibit-item-link';
    }

    $uri = exhibit_builder_exhibit_item_uri($item);
    $text = (!empty($text) ? $text : strip_formatting(metadata($item, array('Dublin Core', 'Title'))));
    $html = '<a href="' . html_escape($uri) . '" '. tag_attributes($props) . '>' . $text . '</a>';
    $html = apply_filters('exhibit_builder_link_to_exhibit_item', $html, array('text' => $text, 'props' => $props, 'item' => $item));
    return $html;
}

/**
 * Return a URL to an item within an exhibit.
 * 
 * @param Item $item
 * @param Exhibit|null $exhibit If null, will use the current exhibit.
 * @return string
 */
function exhibit_builder_exhibit_item_uri($item, $exhibit = null)
{
    if (!$exhibit) {
        $exhibit = get_current_record('exhibit');
    }

    return url(array('slug'=>$exhibit->slug, 'item_id'=>$item->id), 'exhibitItem');
}

/**
 * Return an array of recent exhibits
 *
 * @param int $num The maximum number of exhibits to return
 * @return array
 */
function exhibit_builder_recent_exhibits($num = 10)
{
    return get_records('Exhibit', array('sort'=>'recent'), $num);
}

/**
 * Get an array of available themes
 *
 * @return array
 */
function exhibit_builder_get_themes()
{
    $themeNames = array();

    $themes = apply_filters('browse_themes', Theme::getAllThemes());
    foreach ($themes as $themeDir => $theme) {
        $title = !empty($theme->title) ? $theme->title : $themeDir;
        $themeNames[$themeDir] = $title;
    }

    return $themeNames;
}

/**
 * Return the HTML for summarizing a random featured exhibit
 *
 * @return string
 */
function exhibit_builder_display_random_featured_exhibit()
{
    $html = '<div id="featured-exhibit">';
    $featuredExhibit = exhibit_builder_random_featured_exhibit();
    $html .= '<h2>' . __('Featured Exhibit') . '</h2>';
    if ($featuredExhibit) {
        $html .= get_view()->partial('exhibits/single.php', array('exhibit' => $featuredExhibit));
    } else {
        $html .= '<p>' . __('You have no featured exhibits.') . '</p>';
    }
    $html .= '</div>';
    $html = apply_filters('exhibit_builder_display_random_featured_exhibit', $html);
    return $html;
}

/**
 * Return a random featured exhibit.
 *
 * @return Exhibit|null
 */
function exhibit_builder_random_featured_exhibit()
{
    return get_db()->getTable('Exhibit')->findRandomFeatured();
}

/**
* Returns a link to an exhibit, or exhibit page.
* @uses exhibit_builder_link_to_exhibit
*
* @param string|null $text The text of the link
* @param array $props
* @param ExhibitPage|null $exhibitPage
* @param Exhibit $exhibit|null If null, it uses the current exhibit
* @return string
*/
function link_to_exhibit($text = null, $props = array(), $exhibitPage = null, $exhibit = null)
{
    return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $exhibitPage);
}

function exhibit_builder_exhibits_shortcode($args, $view)
{
    $params = array();

    if (isset($args['is_featured'])) {
        $params['featured'] = $args['is_featured'];
    }

    if (isset($args['sort'])) {
        $params['sort_field'] = $args['sort'];
    }

    if (isset($args['order'])) {
        $params['sort_dir'] = $args['order'];
    }

    if (isset($args['ids'])) {
            $params['range'] = $args['ids'];
    }

    if (isset($args['num'])) {
        $limit = $args['num'];
    } else {
        $limit = 10; 
    }

    $exhibits = get_records('Exhibit', $params, $limit);

    $content = '';
    foreach ($exhibits as $exhibit) {
        $content .= $view->partial('exhibits/single.php', array('exhibit' => $exhibit));
        release_object($exhibit);
    }

    return $content;
}

function exhibit_builder_featured_exhibits_shortcode($args, $view) 
{
    $args['is_featured'] = 1;

    if (!isset($args['num'])) {
        $args['num'] = 1;
    }

    $args['sort'] = 'random';

    return exhibit_builder_exhibits_shortcode($args, $view);
}


