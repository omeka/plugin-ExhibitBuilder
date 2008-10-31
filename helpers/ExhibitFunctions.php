<?php 
///// EXHIBIT FUNCTIONS /////

/**
 * 
 *
 * @return string
 **/
function exhibit_thumbnail($item, $props=array('class'=>'permalink')) 
{	  
    $uri = exhibit_item_uri($item);
     
    $output = '<a href="' . $uri . '">';

    set_current_item($item);
    
    $output .= item_thumbnail($props);

    $output .= '</a>';
	
	return $output;
}

/**
 * Duplication of exhibit_thumbnail()
 *
 * @return string
 **/
function exhibit_fullsize($item, $props=array('class'=>'permalink'))
{
	$uri = exhibit_item_uri($item);
		
	$output = '<a href="' . $uri . '">';

    set_current_item($item);
	
	$output .= item_fullsize($props);
	
	$output .= '</a>';
	
	return $output;
}


function section_has_pages($section) 
{
	return $section->hasPages();
}

function link_to_exhibit($exhibit, $text=null, $props=array(), $section=null, $page = null)
{	
	$uri = exhibit_uri($exhibit, $section, $page);
	
	$text = !empty($text) ? $text : $exhibit->title;
	
	return '<a href="'.$uri.'">' . $text . '</a>';
}

/**
 * @internal This relates to: ExhibitsController::showAction(), ExhibitsController::summaryAction()
 *
 * @return string
 **/
function exhibit_uri($exhibit, $section=null, $page=null)
{
	$exhibit_slug = ($exhibit instanceof Exhibit) ? $exhibit->slug : $exhibit;
	
	$section_slug = ($section instanceof ExhibitSection) ? $section->slug : $section;
	
	$page_slug = ($page instanceof ExhibitPage) ? $page->slug : $page;
	
	//If there is no section slug available, we want to build a URL for the summary page
	if(empty($section_slug)) {
	    $uri = public_uri(array('slug'=>$exhibit_slug), 'exhibitSimple');
	} else {
	    $uri = public_uri(array('slug'=>$exhibit_slug, 'section'=>$section_slug, 'page'=>$page_slug), 'exhibitShow');
	}
		
	return $uri;
}

/**
 * Link to the item within the exhibit.
 * 
 * @param string
 * @return void
 **/
function link_to_exhibit_item($text = null, $props=array())
{	
    $item = get_current_item();
    
	$uri = exhibit_item_uri($item);
	
	$text = (!empty($text) ? $text : strip_formatting(item('Dublin Core', 'Title')));
	
	echo '<a href="' . $uri . '" '. _tag_attributes($props) . '>' . $text . '</a>';
}

function exhibit_item_uri($item, $exhibit=null, $section=null)
{
	if(!$exhibit) {
		$exhibit = Zend_Registry::get('exhibit');
	}
	
	if(!$section) {
		$section = Zend_Registry::get('section');
	}
	
	//If the exhibit has a theme associated with it
	if(!empty($exhibit->theme)) {
		return uri(array('slug'=>$exhibit->slug,'section'=>$section->slug,'item_id'=>$item->id), 'exhibitItem');
	}
	
	else {
		return uri(array('controller'=>'items','action'=>'show','id'=>$item->id), 'id');
	}
	
}

function exhibits($params = array()) {
	return _get_recordset($params, 'exhibits');
}

function recent_exhibits($num = 10) {
	return exhibits(array('recent'=>true,'limit'=>$num));
}

function exhibit($id=null) {
	if(!$id) {
		if(Zend_Registry::isRegistered('exhibit')) {
			return Zend_Registry::get('exhibit');
		}
	}else {
		return get_db()->getTable('Exhibit')->find($id);
	}
}

function exhibit_section($id=null) {
	if(!$id) {
		if(Zend_Registry::isRegistered('section')) {
			return Zend_Registry::get('section');
		}
	}else {
		return get_db()->getTable('ExhibitSection')->find($id);
	}
}

/**
 * Load either the default theme or the chosen exhibit theme, depending
 *
 * @return void
 **/
function exhibit_head()
{
	$exhibit = Zend_Registry::get('exhibit');

	if($exhibit->theme) {
		common('header',compact('exhibit'),'exhibit_themes'.DIRECTORY_SEPARATOR.$exhibit->theme);
	}else {
		head(compact('exhibit'));
	}
	
}

function exhibit_foot()
{
	$exhibit = Zend_Registry::get('exhibit');

	if($exhibit->theme) {
		common('footer',compact('exhibit'),'exhibit_themes'.DIRECTORY_SEPARATOR.$exhibit->theme);
	}else {
		foot(compact('exhibit'));
	}
	
}

function page_text($order, $addTag=true)
{
	$page = Zend_Registry::get('page');

	$text = $page->ExhibitPageEntry[(int) $order]->text;

	return $text;
}

function page_item($order)
{
	$page = Zend_Registry::get('page');

	$item = $page->ExhibitPageEntry[(int) $order]->Item;
	
	if(!$item or !$item->exists()) {
		return null;
	}
	return $item;
}

function exhibit_form_item($item, $orderOnForm=null, $label=null)
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
	   	$html .= text(array('name'=>'Item['.$orderOnForm.']', 'size'=>2), $item->id, $label);
	} else {
	    $html .= '<div class="item_id">' . $item->id . '</div>';
	}
	
	$html .= '</div>';
	return $html;
}

function layout_form_item($order, $label='Enter an Item ID #') {	
	echo exhibit_form_item(page_item($order), $order, $label);
}

function layout_form_text($order, $label='Text') {
	echo '<div class="textfield">';
	echo textarea(array('name'=>'Text['.$order.']','rows'=>'15','cols'=>'80','class'=>'textinput'), page_text($order, false), $label); 
	echo '</div>';
}

/**
 * Get a list of the available exhibit themes
 *
 * @return array
 **/
function get_ex_themes() 
{	
	$path = EXHIBIT_THEMES_DIR;
	$iter = new VersionedDirectoryIterator($path);
	$array = $iter->getValid();
	return array_combine($array,$array);
}

function get_ex_layouts()
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

function exhibit_layout($layout, $input=true)
{	
	//Load the thumbnail image
	$imgFile = web_path_to("exhibit_layouts/$layout/layout.gif");
	
	$page = Zend_Registry::get('page');
    if ($layout == $page->layout) {
        $layout = "current_layout";
    }
	echo '<div class="layout" id="'. $layout .'">';
	echo '<img src="'.$imgFile.'" />';
	if($input) {
		echo '<div class="input">';
		echo '<input type="radio" name="layout" value="'.$layout .'" />';
		echo '</div>';
	}
	echo '<div class="layout-name">'.$layout.'</div>'; 
	echo '</div>';
}

function exhibit_css($file)
{
	if(Zend_Registry::isRegistered('exhibit')) {
		$ex = Zend_Registry::get('exhibit');

		$path = $ex->theme.DIRECTORY_SEPARATOR.$file.'.css';
		
		if(file_exists(EXHIBIT_THEMES_DIR.DIRECTORY_SEPARATOR.$path)) {
			return WEB_EXHIBIT_THEMES.DIRECTORY_SEPARATOR.$path;
		}
	}
	
}

function layout_css($file='layout')
{
	if(Zend_Registry::isRegistered('page')) {
		$p = Zend_Registry::get('page');

		$path = $p->layout.DIRECTORY_SEPARATOR.$file.'.css';
		
		if(file_exists(EXHIBIT_LAYOUTS_DIR.DIRECTORY_SEPARATOR.$path)) {
			return WEB_EXHIBIT_LAYOUTS.DIRECTORY_SEPARATOR.$path;
		}
	}
}

function section_nav()
{
	$exhibit = Zend_Registry::get('exhibit');

	//Use class="section-nav"
	$output = '<ul class="exhibit-section-nav">';

	foreach ($exhibit->Sections as $key => $section) {		
	
		$uri = exhibit_uri($exhibit, $section);
		$output .= '<li><a href="' . $uri . '"' . (is_current($uri) ? ' class="current"' : ''). '>' . h($section->title) . '</a></li>';
	
	}
	
	$output .= '</ul>';
	return $output;
}

function page_nav()
{
	if(!Zend_Registry::isRegistered('section') or !Zend_Registry::isRegistered('page')) {
		return false;
	}
	
	$section = Zend_Registry::get('section');
	
	$currentPage = Zend_Registry::get('page');
		
	$output = '<ul class="exhibit-page-nav">';
	
	$key = 1;
    if($section) {
    	foreach ($section->Pages as $key => $page) {
	
    		$uri = exhibit_uri($section->Exhibit, $section, $page);
		
    		//Create the link (also check if uri matches current uri)
    		$output .= '<li'. ($page->id == $currentPage->id ? ' class="current"' : '').'><a href="'. $uri . '">'. $page->title .'</a></li>';
	
	    }
    }
	$output .= '</ul>';
	
	return $output;
}

function render_exhibit_page()
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

function render_layout_form($layout)
{
/*
		echo '<style>';
	include EXHIBIT_LAYOUTS_DIR.DIRECTORY_SEPARATOR.$layout.DIRECTORY_SEPARATOR.'layout.css';
	echo '</style>';
*/	
	
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
function display_exhibit_thumbnail_gallery($start, $end, $props=array())
{
    $output = '';
    
    for ($i=(int)$start; $i <= (int)$end; $i++) { 
        if (use_exhibit_page_item($i)) {    
    	    $output .= "\n" . '<div class="exhibit-item">';
    	    $output .= link_to_exhibit_item(item_thumbnail($props));
            $output .= '</div>' . "\n";
        }
    }
    
    return $output;
}

function display_random_featured_exhibit()
{
    $html = '<div id="featured-exhibit">';
	$featuredExhibit = random_featured_exhibit();
	$html .= '<h2>Featured Exhibit</h2>';
	if ($featuredExhibit) {
	   $html .= '<h3>' . link_to_exhibit($featuredExhibit) . '</h3>';
	} else {
	   $html .= '<p>You have no featured exhibits.</p>';
	}
	$html .= '</div>';
    return $html;
}

function random_featured_exhibit()
{
    return get_db()->getTable('Exhibit')->findRandomFeatured();
}

/**
 * Links to next exhibit page
 *
 * @return string
 **/
function link_to_next_exhibit_page($text="Next Page --&gt;", $props=array())
{
    $exhibit = Zend_Registry::get('exhibit');
    $section = Zend_Registry::get('section');
    
    // if page object exists, grab link to next exhibit page if exists
    if ($page = Zend_Registry::get('page')) {
    	if($next = $page->next()) {
    		return link_to_exhibit($exhibit, $text, array(), $section, $page->next());
    	}        
    }
}

/**
 * Links to previous exhibit page
 *
 * @return string
 **/
function link_to_previous_exhibit_page($text="&lt;-- Previous Page", $props=array())
{
    $exhibit = Zend_Registry::get('exhibit');
    $section = Zend_Registry::get('section');

    // if page object exists, grab link to previous exhibit page if exists	
	if ($page = Zend_Registry::get('page')) {
    	if($previous = $page->previous()) {
    		return link_to_exhibit($exhibit, $text, array(), $section, $page->previous());
    	}	    
	}
}

function exhibit_display_item($displayFilesOptions = array(), $linkProperties = array())
{
    // $item = page_item($index);
    // set_current_item($item);
    $item = get_current_item();

    // Always just display the first file (may change this in future).
    $fileIndex = 0;
    $linkProperties['href'] = exhibit_item_uri($item);
    $html   = '<a ' . _tag_attributes($linkProperties) . '>';
    $itemHtml  = display_file($item->Files[$fileIndex], $displayFilesOptions);
    if (!$itemHtml) {
        $itemHtml = item('Dublin Core', 'Title');
    }
    $html  .= $itemHtml;
    $html  .= '</a>';
    return $html;
}

/**
 * @todo Needs optimization (shouldn't return the item object every time it's checked).
 * @param integer
 * @return boolean
 **/
function exhibit_page_has_item($index)
{
    return (boolean)page_item($index);
}

function use_exhibit_page_item($index)
{
    $item = page_item($index);
    if ($item instanceof Item) {
        set_current_item($item);
        return $item;
    }
    
    return false;
}

///// END EXHIBIT FUNCTIONS /////
 
?>
