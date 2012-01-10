<?php
/**
 * Returns the current exhibit.
 *
 * @return Exhibit|null
 **/
function exhibit_builder_get_current_exhibit()
{
    return __v()->exhibit;
}

/**
 * Sets the current exhibit.
 *
 * @param Exhibit|null $exhibit
 * @return void
 **/
function exhibit_builder_set_current_exhibit($exhibit = null)
{
    __v()->exhibit = $exhibit;
}

/**
 * Returns whether an exhibit is the current exhibit.
 *
 * @param Exhibit|null $exhibit
 * @return boolean
 **/
function exhibit_builder_is_current_exhibit($exhibit)
{
    $currentExhibit = exhibit_builder_get_current_exhibit();
    return ($exhibit == $currentExhibit || ($exhibit && $currentExhibit && $exhibit->id == $currentExhibit->id));
}

/**
 * Returns a link to the exhibit
 *
 * @param Exhibit $exhibit|null If null, it uses the current exhibit
 * @param string|null $text The text of the link
 * @param array $props
 * @param ExhibitSection|null $exhibitSection
 * @param ExhibitPage|null $exhibitPage
 * @return string
 **/
function exhibit_builder_link_to_exhibit($exhibit = null, $text = null, $props = array(), $exhibitSection = null, $exhibitPage = null)
{   
    if (!$exhibit) {
        $exhibit = exhibit_builder_get_current_exhibit();
    }
    $uri = exhibit_builder_exhibit_uri($exhibit, $exhibitSection, $exhibitPage);
    $text = !empty($text) ? $text : html_escape($exhibit->title);
    return '<a href="' . html_escape($uri) .'" '. _tag_attributes($props) . '>' . $text . '</a>';
}

/**
 * Returns a URI to the exhibit
 *
 * @param Exhibit $exhibit|null If null, it uses the current exhibit.
 * @param ExhibitSection|null $exhibitSection
 * @param ExhibitPage|null $exhibitPage 
 * @internal This relates to: ExhibitsController::showAction(), ExhibitsController::summaryAction()
 * @return string
 **/
function exhibit_builder_exhibit_uri($exhibit = null, $exhibitSection = null, $exhibitPage = null)
{
    if (!$exhibit) {
        $exhibit = exhibit_builder_get_current_exhibit();
    }
    $exhibitSlug = ($exhibit instanceof Exhibit) ? $exhibit->slug : $exhibit;
    $exhibitSectionSlug = ($exhibitSection instanceof ExhibitSection) ? $exhibitSection->slug : $exhibitSection;
    $exhibitPageSlug = ($exhibitPage instanceof ExhibitPage) ? $exhibitPage->slug : $exhibitPage;

    //If there is no section slug available, we want to build a URL for the summary page
    if (empty($exhibitSectionSlug)) {
        $uri = public_uri(array('slug'=>$exhibitSlug), 'exhibitSimple');
    } else {
        $uri = public_uri(array('slug'=>$exhibitSlug, 'section_slug'=>$exhibitSectionSlug, 'page_slug'=>$exhibitPageSlug), 'exhibitShow');
    }
    return $uri;
}

/**
 * Returns a link to the item within the exhibit.
 * 
 * @param string|null $text
 * @param array $props
 * @param Item|null $item If null, will use the current item.
 * @return string
 **/
function exhibit_builder_link_to_exhibit_item($text = null, $props = array(), $item = null)
{   
    if (!$item) {
        $item = get_current_item();
    }

    if (!isset($props['class'])) {
        $props['class'] = 'exhibit-item-link';
    }
    
    $uri = exhibit_builder_exhibit_item_uri($item);
    $text = (!empty($text) ? $text : strip_formatting(item('Dublin Core', 'Title')));
    $html = '<a href="' . html_escape($uri) . '" '. _tag_attributes($props) . '>' . $text . '</a>';
    $html = apply_filters('exhibit_builder_link_to_exhibit_item', $html, $text, $props, $item);
    return $html;
}

/**
 * Returns a URI to the exhibit item
 * 
 * @deprecated since 1.1
 * @param Item $item
 * @param Exhibit|null $exhibit If null, will use the current exhibit.
 * @param ExhibitSection|null $exhibitSection If null, will use the current exhibit section
 * @return string
 **/
function exhibit_builder_exhibit_item_uri($item, $exhibit = null, $exhibitSection = null)
{
    if (!$exhibit) {
        $exhibit = exhibit_builder_get_current_exhibit();
    }

    if (!$exhibitSection) {
        $exhibitSection = exhibit_builder_get_current_section();
    }
    
    //If the exhibit has a theme associated with it
    if (!empty($exhibit->theme)) {
        return uri(array('slug'=>$exhibit->slug,'section_slug'=>$exhibitSection->slug,'item_id'=>$item->id), 'exhibitItem');
    } else {
        return uri(array('controller'=>'items','action'=>'show','id'=>$item->id), 'id');
    }
}

/**
 * Returns an array of exhibits
 * 
 * @param array $params
 * @return array
 **/
function exhibit_builder_get_exhibits($params = array()) 
{
    return get_db()->getTable('Exhibit')->findBy($params);
}

/**
 * Returns an array of recent exhibits
 * 
 * @param int $num The maximum number of exhibits to return
 * @return array
 **/
function exhibit_builder_recent_exhibits($num = 10) 
{
    return exhibit_builder_get_exhibits(array('sort'=>'recent','limit'=>$num));
}

/**
 * Returns an Exhibit by id
 * 
 * @param int $exhibitId The id of the exhibit
 * @return Exhibit
 **/
function exhibit_builder_get_exhibit_by_id($exhibitId) 
{
    return get_db()->getTable('Exhibit')->find($exhibitId);
}

/**
 * Displays the exhibit header
 *
 * @return void
 * @deprecated since 1.0.1
 **/
function exhibit_builder_exhibit_head()
{
	head(compact('exhibit'));
}

/**
 * Displays the exhibit footer
 *
 * @return void
 * @deprecated since 1.0.1
 **/
function exhibit_builder_exhibit_foot()
{
	foot(compact('exhibit'));
}


/**
 * Returns the HTML code of the item attach section of the exhibit form
 *
 * @param Item $item
 * @param int $orderOnForm
 * @param string $label
 * @return string
 **/
function exhibit_builder_exhibit_form_item($item, $orderOnForm = null, $label = null, $includeCaption = true)
{
    $html = '<div class="item-select-outer exhibit-form-element">';  

    if ($item and $item->exists()) {
        set_current_item($item);
        $html .= '<div class="item-select-inner">' . "\n";
        $html .= '<div class="item_id">' . html_escape($item->id) . '</div>' . "\n";
        $html .= '<h2 class="title">' . item('Dublin Core', 'Title') . '</h2>' . "\n";
        if (item_has_files()) {
            $html .=  display_file($item->Files[0], array('linkToFile'=>false, 'imgAttributes' => array('alt' => item('Dublin Core', 'Title'))));
        } 
        
        if ($includeCaption) {
            $html .= exhibit_builder_layout_form_caption($orderOnForm);
        }     
        
        $html .= '</div>' . "\n";      
    } else {
        
        $html .= '<p class="attach-item-link">'
               . __('There is no item attached.')
               . ' <a href="#" class="button">'
               . __('Attach an Item') .'</a></p>' . "\n";
    }
    
    // If this is ordered on the form, make sure the generated form element indicates its order on the form.
    if ($orderOnForm) {
        $id = ($item and $item->exists()) ? $item->id: null;
        $html .= __v()->formHidden('Item['.$orderOnForm.']', $id, array('size'=>2));
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Returns the HTML code for an item on a layout form
 *
 * @param int $order The order of the item
 * @param string $label
 * @return string
 **/
function exhibit_builder_layout_form_item($order, $label = 'Enter an Item ID #') 
{   
    return exhibit_builder_exhibit_form_item(exhibit_builder_page_item($order), $order, $label);
}

/**
 * Returns the HTML code for a textarea on a layout form
 *
 * @param int $order The order of the item
 * @param string $label
 * @return string
 **/
function exhibit_builder_layout_form_text($order, $label = 'Text') 
{
    $html = '<div class="textfield exhibit-form-element">';
    $html .= textarea(array('name'=>'Text['.$order.']','rows'=>'15','cols'=>'70','class'=>'textinput'), exhibit_builder_page_text($order)); 
    $html .= '</div>';
    $html = apply_filters('exhibit_builder_layout_form_text', $html, $order, $label);
    return $html;
}

/**
 * Returns the HTML code for a caption on a layout form
 *
 * @param int $order The order of the item
 * @param string $label
 * @return string
 **/
function exhibit_builder_layout_form_caption($order, $label = null) 
{
    if ($label === null) {
        $label = __('Caption');
    }

    $html = '<div class="caption-container">' . "\n";
    $html .= '<p>' . html_escape($label) . '</p>' . "\n";
    $html .= '<div class="caption">' . "\n";
    $html .= '<label for="Caption['.$order.']">'.$label.'</label>' . "\n";
    $html .= textarea(array('name'=>'Caption['.$order.']','rows'=>'4','cols'=>'30','class'=>'textinput'), exhibit_builder_page_caption($order)); 
    $html .= '</div>' . "\n";
    $html .= '</div>' . "\n";
    
    $html = apply_filters('exhibit_builder_layout_form_caption', $html, $order, $label);
    return $html;
}

/**
 * Returns an array of available themes
 *
 * @return array
 **/
function exhibit_builder_get_ex_themes() 
{
    $themeNames = array();

    $themes = apply_filters('browse_themes', Theme::getAvailable());
    foreach ($themes as $themeDir => $theme) {
        $title = !empty($theme->title) ? $theme->title : $themeDir;
        $themeNames[$themeDir] = $title;
    }

    return $themeNames;
}

/**
 * Returns an array of available exhibit layouts
 *
 * @return array
 **/
function exhibit_builder_get_ex_layouts()
{
    $it = new VersionedDirectoryIterator(EXHIBIT_LAYOUTS_DIR, true);
    $array = $it->getValid();
    natsort($array);
    return $array;
}

/**
 * Returns the HTML code for an exhibit layout
 *
 * @param string $layout The layout name
 * @param boolean $input Whether or not to include the input to select the layout
 * @return string
 **/
function exhibit_builder_exhibit_layout($layout, $input = true)
{   
    //Load the thumbnail image
    try {
        $imgFile = web_path_to(EXHIBIT_LAYOUTS_DIR_NAME . "/$layout/layout.gif");
    } catch (Exception $e) {
        // Thumbnail not found, assuming this folder isn't a layout.
        return;
    }
    
    $exhibitPage = exhibit_builder_get_current_page();
    $isSelected = ($exhibitPage->layout == $layout) and $layout;
    
    $html = '';
    $html .= '<div class="layout' . ($isSelected ? ' current-layout' : '') . '" id="'. html_escape($layout) .'">';
    $html .= '<img src="'. html_escape($imgFile) .'" />';
    if ($input) {
        $html .= '<div class="input">';
        $html .= '<input type="radio" name="layout" value="'. html_escape($layout) .'" ' . ($isSelected ? 'checked="checked"' : '') . '/>';
        $html .= '</div>';
    }
    $html .= '<div class="layout-name">'.html_escape($layout).'</div>'; 
    $html .= '</div>';
    $html = apply_filters('exhibit_builder_exhibit_layout', $html, $layout, $input);
    return $html;
}

/**
 * Returns the web path to the exhibit css
 *
 * @param string $fileName The name of the CSS file (does not include file extension)
 * @return string
 * @deprecated since 1.0.1
 **/
function exhibit_builder_exhibit_css($fileName)
{
	return css($fileName);   
}

/**
 * Returns the web path to the layout css
 *
 * @param string $fileName The name of the CSS file (does not include file extension)
 * @return string
 **/
function exhibit_builder_layout_css($fileName = 'layout')
{
    if ($exhibitPage = exhibit_builder_get_current_page()) {
        return css($fileName, EXHIBIT_LAYOUTS_DIR_NAME . DIRECTORY_SEPARATOR . $exhibitPage->layout);
    }
}

/**
 * Displays an exhibit page
 * 
 * @param ExhibitPage $exhibitPage If null, will use the current exhibit page.
 * @return void
 **/
function exhibit_builder_render_exhibit_page($exhibitPage = null)
{
    if (!$exhibitPage) {
        $exhibitPage = exhibit_builder_get_current_page();
    }
    if ($exhibitPage->layout) {
     include EXHIBIT_LAYOUTS_DIR.DIRECTORY_SEPARATOR.$exhibitPage->layout.DIRECTORY_SEPARATOR.'layout.php';
    } else {
     echo "This page does not have a layout.";
    }
}

/**
 * Displays an exhibit layout form
 * 
 * @param string The name of the layout
 * @return void
 **/
function exhibit_builder_render_layout_form($layout)
{   
    include EXHIBIT_LAYOUTS_DIR.DIRECTORY_SEPARATOR.$layout.DIRECTORY_SEPARATOR.'form.php';
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
function exhibit_builder_display_exhibit_thumbnail_gallery($start, $end, $props = array(), $thumbnailType = 'square_thumbnail')
{
    $html = '';
    for ($i=(int)$start; $i <= (int)$end; $i++) { 
        if (exhibit_builder_use_exhibit_page_item($i)) {    
            $html .= "\n" . '<div class="exhibit-item">';
            $thumbnail = item_image($thumbnailType, $props);
            $html .= exhibit_builder_link_to_exhibit_item($thumbnail);
            $html .= exhibit_builder_exhibit_display_caption($i);
            $html .= '</div>' . "\n";
        }
    }
    $html = apply_filters('exhibit_builder_display_exhibit_thumbnail_gallery', $html, $start, $end, $props, $thumbnailType);
    return $html;
}

/**
 * Returns the HTML of a random featured exhibit
 *
 * @return string
 **/
function exhibit_builder_display_random_featured_exhibit()
{
    $html = '<div id="featured-exhibit">';
    $featuredExhibit = exhibit_builder_random_featured_exhibit();
    $html .= '<h2>' . __('Featured Exhibit') . '</h2>';
    if ($featuredExhibit) {
       $html .= '<h3>' . exhibit_builder_link_to_exhibit($featuredExhibit) . '</h3>'."\n";
       $html .= '<p>'.snippet_by_word_count(exhibit('description', array(), $featuredExhibit)).'</p>';
    } else {
       $html .= '<p>' . __('You have no featured exhibits.') . '</p>';
    }
    $html .= '</div>';
    $html = apply_filters('exhibit_builder_display_random_featured_exhibit', $html);
    return $html;
}

/**
 * Returns a random featured exhibit
 *
 * @return Exhibit
 **/
function exhibit_builder_random_featured_exhibit()
{
    return get_db()->getTable('Exhibit')->findRandomFeatured();
}

/**
 * Returns the html code an exhibit item
 * 
 * @param array $displayFilesOptions
 * @param array $linkProperties
 * @return string
 **/
function exhibit_builder_exhibit_display_item($displayFilesOptions = array(), $linkProperties = array(), $item = null)
{
    if (!$item) {
        $item = get_current_item();
    }
    
    // Always just display the first file (may change this in future).
    $fileIndex = 0;
    
    // Default link href points to the exhibit item page.
    if (!isset($displayFilesOptions['linkAttributes']['href'])) {
        $displayFilesOptions['linkAttributes']['href'] = exhibit_builder_exhibit_item_uri($item);
    }
    
    // Default alt text is the
    if(!isset($displayFileOptions['imgAttributes']['alt'])) {
        $displayFilesOptions['imgAttributes']['alt'] = item('Dublin Core', 'Title', array(), $item);
    }
    
    // Pass null as the 3rd arg so that it doesn't output the item-file div.
    $fileWrapperClass = null;
    $file = $item->Files[$fileIndex];
    if ($file) {
        $html = display_file($file, $displayFilesOptions, $fileWrapperClass);
    } else {
        $html = exhibit_builder_link_to_exhibit_item(null, $linkProperties, $item);
    }
    
    $html = apply_filters('exhibit_builder_exhibit_display_item', $html, $displayFilesOptions, $linkProperties, $item);

    return $html;
}

/**
 * Returns the caption at a given index
 *
 * @param index 
 **/
function exhibit_builder_exhibit_display_caption($index = 1)
{
    $html = '';
    if ($caption = exhibit_builder_page_caption($index)) {
        $html .= '<div class="exhibit-item-caption">'."\n";
        $html .= $caption."\n";
        $html .= '</div>'."\n";
    }
    
    $html = apply_filters('exhibit_builder_exhibit_fullsize', $html, $index);
    
    return $html;
}
/**
 * Returns the HTML code for an exhibit thumbnail image.
 *
 * @param Item $item
 * @param array $props
 * @param int $index The index of the image for the item
 * @return string
 **/
function exhibit_builder_exhibit_thumbnail($item, $props = array('class'=>'permalink'), $index = 0) 
{     
    $uri = exhibit_builder_exhibit_item_uri($item);
    $html = '<a href="' . html_escape($uri) . '">';
    $html .= item_thumbnail($props, $index, $item);
    $html .= '</a>';  
    $html = apply_filters('exhibit_builder_exhibit_thumbnail', $html, $item, $props, $index);
    return $html;
}

/**
 * Returns the HTML code for an exhibit fullsize image.
 *
 * @param Item $item
 * @param array $props
 * @param int $index The index of the image for the item
 * @return string
 **/
function exhibit_builder_exhibit_fullsize($item, $props = array('class'=>'permalink'), $index = 0)
{
    $uri = exhibit_builder_exhibit_item_uri($item);
    $html = '<a href="' . html_escape($uri) . '">';
    $html .= item_fullsize($props, $index, $item);
    $html .= '</a>';
    $html = apply_filters('exhibit_builder_exhibit_fullsize', $html, $item, $props, $index);
    return $html;
}

/**
 * Returns true if a given user can edit a given exhibit.
 * 
 * @param Exhibit|null $exhibit If null, will use the current exhibit
 * @param User|null $user If null, will use the current user.
 * @return boolean
 **/
function exhibit_builder_user_can_edit($exhibit = null, $user = null)
{
    if (!$exhibit) {
        $exhibit = exhibit_builder_get_current_exhibit();
    }
    if (!$user) { 
        $user = current_user();
    }
    $acl = get_acl();

    $canEditSelf = $acl->isAllowed($user, 'ExhibitBuilder_Exhibits', 'editSelf');
    $canEditOthers = $acl->isAllowed($user, 'ExhibitBuilder_Exhibits', 'editAll');

    return (($exhibit->wasAddedBy($user) && $canEditSelf) || $canEditOthers);    
}

/**
 * Returns true if a given user can delete a given exhibit.
 *
 * @param Exhibit|null $exhibit If null, will use the current exhibit
 * @param User|null $user If null, will use the current user.
 * @return boolean
 **/
function exhibit_builder_user_can_delete($exhibit = null, $user = null)
{
    if (!$exhibit) {
        $exhibit = exhibit_builder_get_current_exhibit();
    }
    if (!$user) {
        $user = current_user();
    }
    $acl = get_acl();

    $canDeleteSelf = $acl->isAllowed($user, 'ExhibitBuilder_Exhibits', 'deleteSelf');
    $canDeleteAll = $acl->isAllowed($user, 'ExhibitBuilder_Exhibits', 'deleteAll');

    return (($exhibit->wasAddedBy($user) && $canDeleteSelf) || $canDeleteAll);
}

/**
* Gets the current exhibit
*
* @return Exhibit|null
**/
function get_current_exhibit()
{
    return exhibit_builder_get_current_exhibit();
}

/**
 * Sets the current exhibit
 *
 * @see loop_exhibits()
 * @param Exhibit
 * @return void
 **/
function set_current_exhibit(Exhibit $exhibit)
{
   exhibit_builder_set_current_exhibit($exhibit);
}

/**
 * Sets the exhibits for loop
 *
 * @param array $exhibits
 * @return void
 **/
function set_exhibits_for_loop($exhibits)
{
    __v()->exhibits = $exhibits;
}

/**
 * Get the set of exhibits for the current loop.
 * 
 * @return array
 **/
function get_exhibits_for_loop()
{
    return __v()->exhibits;
}

/**
 * Loops through exhibits assigned to the view.
 * 
 * @return mixed The current exhibit
 */
function loop_exhibits()
{
    return loop_records('exhibits', get_exhibits_for_loop(), 'set_current_exhibit');
}

/**
 * Determine whether or not there are any exhibits in the database.
 * 
 * @return boolean
 **/
function has_exhibits()
{
    return (total_exhibits() > 0);    
}

/**
 * Determines whether there are any exhibits for loop.
 * @return boolean
 */
function has_exhibits_for_loop()
{
    $view = __v();
    return ($view->exhibits and count($view->exhibits));
}

/**
  * Returns the total number of exhibits in the database
  *
  * @return integer
  **/
 function total_exhibits() 
 {	
 	return get_db()->getTable('Exhibit')->count();
 }

/**
* Gets a property from an exhibit
*
* @param string $propertyName
* @param array $options
* @param Exhibit $exhibit  The exhibit
* @return mixed The exhibit property value
**/
function exhibit($propertyName, $options = array(), $exhibit = null)
{
    if (!$exhibit) {
        $exhibit = get_current_exhibit();
    }
    $propertyName = Inflector::underscore($propertyName);
	if (property_exists(get_class($exhibit), $propertyName)) {
	    return $exhibit->$propertyName;
	} else {
	    return null;
	}
}

/**
* Returns a link to an exhibit, exhibit section, or exhibit page.
* @uses exhibit_builder_link_to_exhibit
*
* @param string|null $text The text of the link
* @param array $props
* @param ExhibitSection|null $exhibitSection
* @param ExhibitPage|null $exhibitPage
* @param Exhibit $exhibit|null If null, it uses the current exhibit
* @return string
**/
function link_to_exhibit($text = null, $props = array(), $exhibitSection = null, $exhibitPage = null, $exhibit = null)
{
	return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $exhibitSection, $exhibitPage);
}
