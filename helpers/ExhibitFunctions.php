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
          . '<a href="../edit-page-content/' . $pageId . '">' . html_escape($page->title) . '</a>'
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

    //If the exhibit has a theme associated with it
    if (!empty($exhibit->theme)) {
        return url(array('slug'=>$exhibit->slug, 'item_id'=>$item->id), 'exhibitItem');
    } else {
        return url(array('controller'=>'items','action'=>'show','id'=>$item->id), 'id');
    }
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
 * Get the HTML for an item attachment on a layout form.
 *
 * @param int $order The index of this layout element.
 * @return string
 */
function exhibit_builder_layout_form_item($order)
{
    $attachment = exhibit_builder_page_attachment($order);
    $item = null;
    $file = null;
    $caption = null;

    if ($attachment) {
        $item = $attachment['item'];
        if ($attachment['file_specified']) {
            $file = $attachment['file'];
        }
        $caption = $attachment['caption'];
    }

    return exhibit_builder_form_attachment($item, $file, $caption, $order);
}

/**
 * Get the HTML for a text input on a layout form
 *
 * @param int $order The index of this layout element.
 * @return string
 */
function exhibit_builder_layout_form_text($order)
{
    $html = '<div class="textfield exhibit-form-element">';
    $html .= get_view()->formTextarea("Text[$order]",
        exhibit_builder_page_text($order), array('rows' => '15','cols' => '70'));
    $html .= '</div>';
    $html = apply_filters('exhibit_builder_layout_form_text', $html,
        array('order' => $order));
    return $html;
}

/**
 * Get the HTML for "attach an item" section of the exhibit form
 *
 * @param Item $item The currently attached item, if any
 * @param File $file The currently attached file, if any
 * @param string|boolean $caption The current caption. If false, don't display
 *  the caption form.
 * @param int $order Layout form order. If omitted, don't output form elements
 * @return string
 */
function exhibit_builder_form_attachment($item = null, $file = null, $caption = null, $order = null)
{
    if ($item) {
        $html = '<div class="item-select-outer exhibit-form-element" data-item-id="' . $item->id . '">'
              . '<div class="item-select-inner">'
              . '<h4 class="title">'
              . metadata($item, array('Dublin Core', 'Title'))
              . '</h4>';
        if (metadata($item, 'has files')) {
            if ($file) {
                $html .= '<div class="item-file">' 
                    . file_image('square_thumbnail', array(), $file)
                    . '</div>';
            } else {
                foreach ($item->Files as $displayFile) {
                    if ($displayFile->hasThumbnail()) {
                        $html .= '<div class="item-file">'
                            . file_image('square_thumbnail', array(), $displayFile)
                            . '</div>';
                    }
                }
            }
            if ($order) {
                $html .= exhibit_builder_form_file($order, $item, $file);
            }
        }
        
        if ($caption !== false) {
            $html .= exhibit_builder_form_caption($order, $caption);
        }

        $html .= '</div>' . "\n";
    } else {
        $html = '<div class="item-select-outer exhibit-form-element">'
              . '<p class="attach-item-link">'
              . __('There is no item attached.')
              . ' <a href="#" class="green button">'
              . __('Attach an Item') .'</a></p>' . "\n";
    }

    // If an order was passed, this is an input on a layout form, so include the
    // form element to indicate what file is attached here.
    if ($order) {
        $itemId = ($item) ? $item->id : null;
        $html .= get_view()->formHidden("Item[$order]", $itemId);
    }

    $html .= '</div>';
    return $html;
}

/**
 * Get the HTML for a caption form input.
 *
 * @param int $order The order of the attachment for this caption
 * @param string $caption The existing caption, if any
 * @return string
 */
function exhibit_builder_form_caption($order, $caption = null)
{
    $label = __('Caption');

    $html = '<div class="caption-container">'
          . '<label for="Caption-' . $order.'">' . $label . '</label>'
          . get_view()->formTextarea("Caption[$order]", $caption,
                array('rows'=>'4','cols'=>'30'))
          . '</div>';

    $html = apply_filters('exhibit_builder_form_caption', $html,
        array('order' => $order, 'caption' => $caption));
    return $html;
}

/**
 * Get the HTML for choosing a file for an attachment.
 *
 * @param int $order The order of the attachment for this caption
 * @param Item $item The item for this attachment
 * @param File $currentFile The currently attached file
 * @return string
 */
function exhibit_builder_form_file($order, $item, $currentFile = null)
{
    $options = array('' => __('Select a File'));
    $files = $item->Files;
    if (!$files || count($files) == 1) {
        return '';
    }

    foreach ($files as $file) {
        $label = metadata($file, array('Dublin Core', 'Title'),
            array('no_escape' => true));
        if (!$label) {
            $label = $file->original_filename;
        }

        $options[$file->id] = $label;
    }

    $currentId = $currentFile ? $currentFile->id : null;

    return get_view()->formSelect("File[$order]", $currentId,
        array('multiple' => false), $options);
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
 * Get an array of available exhibit layouts
 *
 * @return array
 */
function exhibit_builder_get_layouts()
{
    $iterator = new VersionedDirectoryIterator(EXHIBIT_LAYOUTS_DIR, true);
    $array = $iterator->getValid();
    natsort($array);
    return $array;
}

/**
 * Get the HTML code for choosing an exhibit layout
 *
 * @param string $layout The layout name
 * @param boolean $input Whether or not to include the input to select the layout
 * @return string
 */
function exhibit_builder_layout($layout, $input = true)
{
    //Load the thumbnail image
    try {
        $imgFile = web_path_to(EXHIBIT_LAYOUTS_DIR_NAME . "/$layout/layout.gif");
    } catch (Exception $e) {
        // Thumbnail not found, assuming this folder isn't a layout.
        return;
    }

    $exhibitPage = get_current_record('exhibit_page');
    $isSelected = ($exhibitPage->layout == $layout) and $layout;
    $iniPath = EXHIBIT_LAYOUTS_DIR . "/$layout/layout.ini";
    if (file_exists($iniPath) && is_readable($iniPath)) {
        $layoutIni = new Zend_Config_Ini($iniPath, 'layout');
        $layoutName = $layoutIni->name;
    }
    
    $html = '<div class="layout' . ($isSelected ? ' current-layout' : '') . '" id="'. html_escape($layout) .'">'
          . '<img src="'. html_escape($imgFile) .'" />';

    if ($input) {
        $html .= '<div class="input">'
               . '<input type="radio" name="layout" value="'. html_escape($layout) .'" ' . ($isSelected ? 'checked="checked"' : '') . '/>'
               . '</div>';
    }

    $html .= '<div class="layout-name">' . html_escape($layoutName) . '</div>'
           . '</div>';
           
    return apply_filters('exhibit_builder_layout', $html,
        array('layout' => $layout, 'input' => $input));
}

/**
 * Return the web path to the layout css
 *
 * @param string $fileName The name of the CSS file (without file extension)
 * @return string
 */
function exhibit_builder_layout_css($fileName = 'layout')
{
    if ($exhibitPage = get_current_record('exhibit_page', false)) {
        return css_src($fileName, EXHIBIT_LAYOUTS_DIR_NAME . '/' . $exhibitPage->layout);
    }
}

/**
 * Display an exhibit page
 *
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page.
 */
function exhibit_builder_render_exhibit_page($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_record('exhibit_page');
    }
    if ($exhibitPage->layout) {
        include EXHIBIT_LAYOUTS_DIR . '/' . $exhibitPage->layout . '/layout.php';
    } else {
        echo "This page does not have a layout.";
    }
}

/**
 * Displays an exhibit layout form
 *
 * @param string The name of the layout
 * @return void
 */
function exhibit_builder_render_layout_form($layout)
{
    include EXHIBIT_LAYOUTS_DIR . '/' . $layout . '/form.php';
}

/**
 * Returns HTML for a set of linked thumbnails for the items on a given exhibit page.  Each
 * thumbnail is wrapped with a div of class = "exhibit-item"
 *
 * @param int $start The range of items on the page to display as thumbnails
 * @param int $end The end of the range
 * @param array $props Properties to apply to the <img> tag for the thumbnails
 * @param string $thumbnailType The type of thumbnail to display
 * @return string HTML output
 **/
function exhibit_builder_thumbnail_gallery($start, $end, $props = array(), $thumbnailType = 'square_thumbnail')
{
    $html = '';
    for ($i = (int)$start; $i <= (int)$end; $i++) {
        if ($attachment = exhibit_builder_page_attachment($i)) {
            $html .= "\n" . '<div class="exhibit-item">';
            if ($attachment['file']) {
                $thumbnail = file_image($thumbnailType, $props, $attachment['file']);
                $html .= exhibit_builder_link_to_exhibit_item($thumbnail, array(), $attachment['item']);
            }
            $html .= exhibit_builder_attachment_caption($attachment);
            $html .= '</div>' . "\n";
        }
    }
    
    return apply_filters('exhibit_builder_thumbnail_gallery', $html,
        array('start' => $start, 'end' => $end, 'props' => $props, 'thumbnail_type' => $thumbnailType));
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
       $html .= '<h3>' . exhibit_builder_link_to_exhibit($featuredExhibit) . '</h3>'."\n";
       $html .= '<p>'.snippet_by_word_count(metadata($featuredExhibit, 'description', array('no_escape' => true))).'</p>';
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
 * Return HTML for displaying an attached item on an exhibit page.
 *
 * @see exhibit_builder_page_attachment for attachment array contents
 * @param array $attachment The attachment.
 * @param array $fileOptions Options for file_markup when displaying a file
 * @param array $linkProperties Attributes for use when linking to an item
 * @return string
 */
function exhibit_builder_attachment_markup($attachment, $fileOptions, $linkProperties)
{
    if (!$attachment) {
        return '';
    }

    $item = $attachment['item'];
    $file = $attachment['file'];

    if (!isset($fileOptions['linkAttributes']['href'])) {
        $fileOptions['linkAttributes']['href'] = exhibit_builder_exhibit_item_uri($item);
    }

    if (!isset($fileOptions['imgAttributes']['alt'])) {
        $fileOptions['imgAttributes']['alt'] = metadata($item, array('Dublin Core', 'Title'));
    }
    
    if ($file) {
        $html = file_markup($file, $fileOptions, null);
    } else if($item) {
        $html = exhibit_builder_link_to_exhibit_item(null, $linkProperties, $item);
    }

    $html .= exhibit_builder_attachment_caption($attachment);

    return apply_filters('exhibit_builder_attachment_markup', $html,
        compact('attachment', 'fileOptions', 'linkProperties')
    );
}

/**
 * Return HTML for displaying an attachment's caption.
 *
 * @see exhibit_builder_page_attachment for attachment array contents
 * @param array $attachment The attachment
 * @return string
 */
function exhibit_builder_attachment_caption($attachment)
{
    if (!is_string($attachment['caption']) || $attachment['caption'] == '') {
        return '';
    }

    $html = '<div class="exhibit-item-caption">'
          . $attachment['caption']
          . '</div>';

    return apply_filters('exhibit_builder_caption', $html, array(
        'attachment' => $attachment
    ));
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
