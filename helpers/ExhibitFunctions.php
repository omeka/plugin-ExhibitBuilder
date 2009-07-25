<?php 
///// EXHIBIT FUNCTIONS /////

/**
 * Returns the HTML code for an exhibit thumbnail image.
 *
 * @param Item $item
 * @param array $props
 * @param int $index The index of the image for the item
 * @return string
 **/
function exhibit_builder_exhibit_thumbnail($item, $props=array('class'=>'permalink'), $index=0) 
{     
    $uri = exhibit_builder_exhibit_item_uri($item);
    $html = '<a href="' . $uri . '">';
    $html .= item_thumbnail($props, $index, $item);
    $html .= '</a>';    
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
function exhibit_builder_exhibit_fullsize($item, $props=array('class'=>'permalink'), $index=0)
{
    $uri = exhibit_builder_exhibit_item_uri($item);
    $html = '<a href="' . $uri . '">';
    $html .= item_fullsize($props, $index, $item);
    $html .= '</a>';
    return $html;
}

/**
 * Returns whether the section has pages.
 *
 * @param ExhibitSection $section
 * @return boolean
 **/
function exhibit_builder_section_has_pages($section) 
{
    return $section->hasPages();
}

/**
 * Returns a link to the exhibit
 *
 * @param Exhibit $exhibit
 * @param string $text The text of the link
 * @param array $props
 * @param ExhibitSection $section
 * @param ExhibitPage $page
 * @return boolean
 **/
function exhibit_builder_link_to_exhibit($exhibit, $text=null, $props=array(), $section=null, $page = null)
{   
    $uri = exhibit_builder_exhibit_uri($exhibit, $section, $page);
    $text = !empty($text) ? $text : $exhibit->title;
    return '<a href="'.$uri.'">' . $text . '</a>';
}

/**
 * Returns a URI to the exhibit
 *
 * @param Exhibit $exhibit
 * @param ExhibitSection $section
 * @param ExhibitPage $page 
 * @internal This relates to: ExhibitsController::showAction(), ExhibitsController::summaryAction()
 * @return string
 **/
function exhibit_builder_exhibit_uri($exhibit, $section=null, $page=null)
{
    $exhibit_slug = ($exhibit instanceof Exhibit) ? $exhibit->slug : $exhibit;
    $section_slug = ($section instanceof ExhibitSection) ? $section->slug : $section;
    $page_slug = ($page instanceof ExhibitPage) ? $page->slug : $page;

    //If there is no section slug available, we want to build a URL for the summary page
    if (empty($section_slug)) {
        $uri = public_uri(array('slug'=>$exhibit_slug), 'exhibitSimple');
    } else {
        $uri = public_uri(array('slug'=>$exhibit_slug, 'section'=>$section_slug, 'page'=>$page_slug), 'exhibitShow');
    }
    return $uri;
}

/**
 * Returns a link to the item within the exhibit.
 * 
 * @param string $text
 * @param array $props
 * @return string
 **/
function exhibit_builder_link_to_exhibit_item($text = null, $props=array('class' => 'exhibit-item-link'))
{   
    $item = get_current_item();
    $uri = exhibit_builder_exhibit_item_uri($item);
    $text = (!empty($text) ? $text : strip_formatting(item('Dublin Core', 'Title')));
    return '<a href="' . $uri . '" '. _tag_attributes($props) . '>' . $text . '</a>';
}

/**
 * Returns a URI to the exhibit item
 * 
 * @param Item $item
 * @param Exhibit $exhibit
 * @param ExhibitSection $section
 * @return string
 **/
function exhibit_builder_exhibit_item_uri($item, $exhibit=null, $section=null)
{
    if (!$exhibit) {
        $exhibit = Zend_Registry::get('exhibit');
    }
    
    if (!$section) {
        $section = Zend_Registry::get('section');
    }
    
    //If the exhibit has a theme associated with it
    if (!empty($exhibit->theme)) {
        return uri(array('slug'=>$exhibit->slug,'section'=>$section->slug,'item_id'=>$item->id), 'exhibitItem');
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
    return exhibit_builder_get_exhibits(array('recent'=>true,'limit'=>$num));
}

/**
 * Returns an exhibit based on the id
 * 
 * @param int $id The id of the exhibit
 * @return Exhibit
 **/
function exhibit_builder_get_exhibit_by_id($id=null) 
{
    if(!$id) {
        if(Zend_Registry::isRegistered('exhibit')) {
            return Zend_Registry::get('exhibit');
        }
    } else {
        return get_db()->getTable('Exhibit')->find($id);
    }
}

/**
 * Returns an ExhibitSection
 *
 * @param $id The id of the exhibit section
 * @return ExhibitSection
 **/
function exhibit_builder_exhibit_section($id=null) 
{
    if (!$id) {
        if (Zend_Registry::isRegistered('section')) {
            return Zend_Registry::get('section');
        }
    } else {
        return get_db()->getTable('ExhibitSection')->find($id);
    }
}

/**
 * Displays the exhibit header
 *
 * @return void
 **/
function exhibit_builder_exhibit_head()
{
    $exhibit = Zend_Registry::get('exhibit');
    if ($exhibit->theme) {
        common('header',compact('exhibit'), EXHIBIT_THEMES_DIR_NAME.DIRECTORY_SEPARATOR.$exhibit->theme);
    } else {
        head(compact('exhibit'));
    }
}

/**
 * Displays the exhibit footer
 *
 * @return void
 **/
function exhibit_builder_exhibit_foot()
{
    $exhibit = Zend_Registry::get('exhibit');
    if ($exhibit->theme) {
        common('footer',compact('exhibit'), EXHIBIT_THEMES_DIR_NAME.DIRECTORY_SEPARATOR.$exhibit->theme);
    } else {
        foot(compact('exhibit'));
    }
}

/**
 * Returns the text of the exhibit page
 *
 * @param int $order
 * @param bool $addTag
 * @return string
 **/
function exhibit_builder_page_text($order, $addTag=true)
{
    $page = Zend_Registry::get('page');
    $text = $page->ExhibitPageEntry[(int) $order]->text;
    return $text;
}

/**
 * Returns the item of the exhibit page
 *
 * @param int $order
 * @return Item
 **/
function exhibit_builder_page_item($order)
{
    $page = Zend_Registry::get('page');
    $item = $page->ExhibitPageEntry[(int) $order]->Item;
    if (!$item or !$item->exists()) {
        return null;
    }
    return $item;
}

/**
 * Returns the HTML code of the item drag and drop section of the exhibit form
 *
 * @param Item $item
 * @param int $orderOnForm
 * @param string $label
 * @return string
 **/
function exhibit_builder_exhibit_form_item($item, $orderOnForm=null, $label=null)
{
    $html = '<div class="item-drop">';  

    if($item and $item->exists()) {
        set_current_item($item);
        $html .= '<div class="item-drag"><div class="item_id">' . $item->id . '</div>';
        $html .=  item_has_thumbnail() ? item_square_thumbnail() : '<div class="title">' . item('Dublin Core', 'Title', ', ') . '</div>';
        $html .= '</div>';      
    }
    
    // If this is ordered on the form, make sure the generated form element indicates its order on the form.
    if ($orderOnForm) {
        $html .= __v()->formText('Item['.$orderOnForm.']', $item->id, array('size'=>2));
    } else {
        $html .= '<div class="item_id">' . $item->id . '</div>';
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
function exhibit_builder_layout_form_item($order, $label='Enter an Item ID #') 
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
function exhibit_builder_layout_form_text($order, $label='Text') 
{
    $html = '<div class="textfield">';
    $html .= textarea(array('name'=>'Text['.$order.']','rows'=>'15','cols'=>'70','class'=>'textinput'), exhibit_builder_page_text($order, false)); 
    $html .= '</div>';
    return $html;
}

/**
 * Returns an array of available exhibit themes
 *
 * @return array
 **/
function exhibit_builder_get_ex_themes() 
{   
    $path = EXHIBIT_THEMES_DIR;
    $iter = new VersionedDirectoryIterator($path);
    $array = $iter->getValid();
    return array_combine($array,$array);
}

/**
 * Returns an array of available exhibit layouts
 *
 * @return array
 **/
function exhibit_builder_get_ex_layouts()
{
    $path = EXHIBIT_LAYOUTS_DIR;
    $it = new VersionedDirectoryIterator($path,false);
    $array = $it->getValid();
    
    //strip off file extensions
    foreach ($array as $k=>$file) {
        $array[$k] = array_shift(explode('.',$file));
    }
    
    natsort($array);
    
    //get rid of duplicates
    $array = array_flip(array_flip($array));
    return $array;
}

/**
 * Returns the HTML code for an exhibit layout
 *
 * @param string $layout The layout name
 * @param bool $input Whether or not to include the input to select the layout
 * @return string
 **/
function exhibit_builder_exhibit_layout($layout, $input=true)
{   
    //Load the thumbnail image
    $imgFile = web_path_to(EXHIBIT_LAYOUTS_DIR_NAME . "/$layout/layout.gif");
    
    $page = Zend_Registry::get('page');
    $isSelected = ($page->layout == $layout) and $layout;
    
    $html = '';
    $html .= '<div class="layout' . ($isSelected ? ' current-layout' : '') . '" id="'. $layout .'">';
    $html .= '<img src="'.$imgFile.'" />';
    if($input) {
        $html .= '<div class="input">';
        $html .= '<input type="radio" name="layout" value="'.$layout .'" ' . ($isSelected ? 'checked="checked"' : '') . '/>';
        $html .= '</div>';
    }
    $html .= '<div class="layout-name">'.$layout.'</div>'; 
    $html .= '</div>';
    return $html;
}

/**
 * Returns the web path to the exhibit css
 *
 * @return string
 **/
function exhibit_builder_exhibit_css($file)
{
    if (Zend_Registry::isRegistered('exhibit')) {
        $ex = Zend_Registry::get('exhibit');
        return css($file, EXHIBIT_THEMES_DIR_NAME . DIRECTORY_SEPARATOR . $ex->theme);
    }   
}

/**
 * Returns the web path to the layout css
 *
 * @return string
 **/
function exhibit_builder_layout_css($file='layout')
{
    if(Zend_Registry::isRegistered('page')) {
        $p = Zend_Registry::get('page');
        if(!empty($p)) {
            return css($file, EXHIBIT_LAYOUTS_DIR_NAME . DIRECTORY_SEPARATOR . $p->layout);
        }
    }
}

/**
 * Returns the HTML code of the exhibit section navigation
 *
 * @return string
 **/
function exhibit_builder_section_nav()
{
    $exhibit = Zend_Registry::get('exhibit');
    //Use class="section-nav"
    $html = '<ul class="exhibit-section-nav">';
    foreach ($exhibit->Sections as $key => $section) {      
        $uri = exhibit_builder_exhibit_uri($exhibit, $section);
        $html .= '<li' . (is_current_uri($uri) ? ' class="current"' : ''). '><a href="' . $uri . '">' . html_escape($section->title) . '</a></li>';
    }
    $html .= '</ul>';
    return $html;
}

/**
 * Returns the HTML code of the exhibit page navigation
 *
 * @return string
 **/
function exhibit_builder_page_nav()
{
    if(!Zend_Registry::isRegistered('section') or !Zend_Registry::isRegistered('page')) {
        return false;
    }
    $section = Zend_Registry::get('section');
    $currentPage = Zend_Registry::get('page');
    $html = '<ul class="exhibit-page-nav">';
    $key = 1;
    if($section) {
        foreach ($section->Pages as $key => $page) {
            $uri = exhibit_builder_exhibit_uri($section->Exhibit, $section, $page);
            //Create the link (also check if uri matches current uri)
            $html .= '<li'. ($page->id == $currentPage->id ? ' class="current"' : '').'><a href="'. $uri . '">'. $page->title .'</a></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

/**
 * Displays an exhibit page
 * 
 * @return void
 **/
function exhibit_builder_render_exhibit_page()
{
    $exhibit = Zend_Registry::get('exhibit');
    try {
        $section = Zend_Registry::get('section');
        $page = Zend_Registry::get('page');
        if ($page->layout) {
         include EXHIBIT_LAYOUTS_DIR.DIRECTORY_SEPARATOR.$page->layout.DIRECTORY_SEPARATOR.'layout.php';
        } else {
         echo "this section has no pages added to it yet";
        }
    } catch (Exception $e) {}
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
 * A set of linked thumbnails for the items on a given exhibit page.  Each 
 * thumbnail is wrapped with a div of class = "exhibit-item"
 *
 * @param int $start The range of items on the page to display as thumbnails
 * @param int $end The end of the range
 * @param array $props Properties to apply to the <img> tag for the thumbnails
 * @return string HTML output
 **/
function exhibit_builder_display_exhibit_thumbnail_gallery($start, $end, $props=array(), $thumbnail_type="square_thumbnail")
{
    $output = '';
    
    for ($i=(int)$start; $i <= (int)$end; $i++) { 
        if (exhibit_builder_use_exhibit_page_item($i)) {    
            $output .= "\n" . '<div class="exhibit-item">';
            $thumbnail = item_image($thumbnail_type, $props);
            $output .= exhibit_builder_link_to_exhibit_item($thumbnail);
            $output .= '</div>' . "\n";
        }
    }
    
    return $output;
}

/**
 * Returns the html of a random featured exhibit
 *
 * @return string
 **/
function exhibit_builder_display_random_featured_exhibit()
{
    $html = '<div id="featured-exhibit">';
    $featuredExhibit = exhibit_builder_random_featured_exhibit();
    $html .= '<h2>Featured Exhibit</h2>';
    if ($featuredExhibit) {
       $html .= '<h3>' . exhibit_builder_link_to_exhibit($featuredExhibit) . '</h3>';
    } else {
       $html .= '<p>You have no featured exhibits.</p>';
    }
    $html .= '</div>';
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
 * Returns a link to the next exhibit page
 *
 * @param string $text The label for the next page link
 * @param array $props
 * @return string
 **/
function exhibit_builder_link_to_next_exhibit_page($text="Next Page &rarr;", $props=array())
{
    $exhibit = Zend_Registry::get('exhibit');
    $section = Zend_Registry::get('section');
    
    // if page object exists, grab link to next exhibit page if exists
    if ($page = Zend_Registry::get('page')) {
        if($next = $page->next()) {
            return exhibit_builder_link_to_exhibit($exhibit, $text, array(), $section, $page->next());
        }        
    }
}

/**
 * Returns a link to the previous exhibit page
 *
 * @param string $text The label for the previous page link
 * @param array $props
 * @return string
 **/
function exhibit_builder_link_to_previous_exhibit_page($text="&larr; Previous Page", $props=array())
{
    $exhibit = Zend_Registry::get('exhibit');
    $section = Zend_Registry::get('section');

    // if page object exists, grab link to previous exhibit page if exists  
    if ($page = Zend_Registry::get('page')) {
        if($previous = $page->previous()) {
            return exhibit_builder_link_to_exhibit($exhibit, $text, array(), $section, $page->previous());
        }       
    }
}

/**
 * Returns the html code an exhibit item
 * 
 * @param array $displayFilesOptions
 * @param array $linkProperties
 * @return string
 **/
function exhibit_builder_exhibit_display_item($displayFilesOptions = array(), $linkProperties = array())
{
    $item = get_current_item();

    // Always just display the first file (may change this in future).
    $fileIndex = 0;
    $linkProperties['href'] = exhibit_builder_exhibit_item_uri($item);
    
    // Don't link to the file b/c it overrides the link to the item.
    $displayFilesOptions['linkToFile'] = false;
    
    $html = '<a ' . _tag_attributes($linkProperties) . '>';
    
    // Pass null as the 3rd arg so that it doesn't output the item-file div.
    $fileWrapperClass = null;
    $itemHtml  = display_file($item->Files[$fileIndex], $displayFilesOptions, $fileWrapperClass);
    if (!$itemHtml) {
        $itemHtml = item('Dublin Core', 'Title');
    }
    $html .= $itemHtml;
    $html .= '</a>';
    return $html;
}

/**
 * Returns whether an exhibit page has an item
 * 
 * @todo Needs optimization (shouldn't return the item object every time it's checked).
 * @param integer
 * @return boolean
 **/
function exhibit_builder_exhibit_page_has_item($index)
{
    return (boolean)exhibit_builder_page_item($index);
}

/**
 * Returns an item at the specified index of an exhibit page.  If no item exists on the page, it returns false.
 * 
 * @param integer $index The index of the page item
 * @return Item|boolean
 **/
function exhibit_builder_use_exhibit_page_item($index)
{
    $item = exhibit_builder_page_item($index);
    if ($item instanceof Item) {
        set_current_item($item);
        return $item;
    }
    
    return false;
}

/**
 * Returns the html code that lists the exhibits
 * 
 * @return string
 **/
function exhibit_builder_show_exhibit_list()
{
    $exhibits = exhibit_builder_get_exhibits();
    if($exhibits):
    ob_start();
    foreach( $exhibits as $key=>$exhibit ): ?>
    <div class="exhibit <?php if($key%2==1) echo ' even'; else echo ' odd'; ?>">
        <h2><?php echo exhibit_builder_link_to_exhibit($exhibit); ?></h2>
        <div class="description"><?php echo $exhibit->description; ?></div>
        <p class="tags"><?php echo tag_string($exhibit, uri('exhibits/browse/tag/')); ?></p>
    </div>
<?php endforeach; else: ?>
    <p>There are no exhibits.</p>
<?php endif;
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

function exhibit_builder_user_can_edit($exhibit, $user = null)
{
    if(!$user) $user = current_user();
    return (($exhibit->wasAddedBy($user) && get_acl()->checkUserPermission('ExhibitBuilder_Exhibits', 'editSelf')) || 
         get_acl()->checkUserPermission('ExhibitBuilder_Exhibits', 'editAll'));
} 