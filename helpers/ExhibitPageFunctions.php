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
function exhibit_builder_set_current_page($exhibitPage=null)
{
    __v()->exhibitPage = $exhibitPage;
}

/**
 * Returns whether an exhibit page is the current exhibit page.
 *
 * @param ExhibitPage|null $page
 * @return boolean
 **/
function exhibit_builder_is_current_page($page)
{
    $currentPage = exhibit_builder_get_current_page();
    return ($page === $currentPage || ($page && $currentPage && $page->id == $currentPage->id));
}

/**
 * Returns the text of the exhibit page
 *
 * @param int $order
 * @param bool $addTag
 * @param ExhibitPage|null If null, it will use the current exhibit page
 * @return string
 **/
function exhibit_builder_page_text($order, $addTag=true, $page=null)
{
    if (!$page) {
        $page = exhibit_builder_get_current_page();
    }
    $text = $page->ExhibitPageEntry[(int) $order]->text;
    return $text;
}

/**
 * Returns an item on the exhibit page.
 *
 * @param int $order
 * @param ExhibitPage|null $page If null, will use the current exhibit page
 * @return Item
 **/
function exhibit_builder_page_item($order, $page = null)
{
    if (!$page) {
        $page = exhibit_builder_get_current_page();
    }
    $item = $page->ExhibitPageEntry[(int) $order]->Item;
    if (!$item or !$item->exists()) {
        return null;
    }
    return $item;
}

/**
 * Returns the HTML code of the exhibit page navigation
 * 
 * @param ExhibitSection|null $section If null, will use the current exhibit section
 * @param string $linkTextType
 * @return string
 **/
function exhibit_builder_page_nav($section = null, $linkTextType='title')
{
    $linkTextType = strtolower(trim($linkTextType));
    if (!$section) {
        if (!($section = exhibit_builder_get_current_section())) {
            return;
        }
    }
    if ($section->hasPages()) {
        $html = '<ul class="exhibit-page-nav">';
        foreach ($section->Pages as $page) {
            switch($linkTextType) {
                case 'order':
                    $linkText = $page->order;
                    break;
                case 'title':
                default:
                    $linkText = $page->title;
                    break;
                
            }
            $html .= '<li'. (exhibit_builder_is_current_page($page) ? ' class="current"' : '').'><a class="exhibit-page-title" href="'. html_escape(exhibit_builder_exhibit_uri($section->Exhibit, $section, $page)) . '">'. html_escape($linkText) .'</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
    return false;
}

/**
 * Returns a link to the next exhibit page
 *
 * @param string $text The label for the next page link
 * @param array $props
 * @param ExhibitPage $page If null, will use the current exhibit page
 * @return string
 **/
function exhibit_builder_link_to_next_exhibit_page($text="Next Page &rarr;", $props=array(), $page = null)
{
    if (!$page) {
        $page = exhibit_builder_get_current_page();
    }
    
    $section = exhibit_builder_get_exhibit_section_by_id($page->section_id);
    $exhibit = exhibit_builder_get_exhibit_by_id($section->exhibit_id);
    
    if(!isset($props['class'])) {
        $props['class'] = 'next-page';
    }
    
    // if page object exists, grab link to next exhibit page if exists. If it doesn't, grab
    // a link to the first page on the next exhibit section, if it exists.
    if ($nextPage = $page->next()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $section, $nextPage);
    } elseif ($nextSection = $section->next()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $nextSection);
    }
}

/**
 * Returns a link to the previous exhibit page
 *
 * @param string $text The label for the previous page link
 * @param array $props
 * @param ExhibitPage $page If null, will use the current exhibit page
 * @return string
 **/
function exhibit_builder_link_to_previous_exhibit_page($text="&larr; Previous Page", $props=array(), $page = null)
{
    if (!$page) {
        $page = exhibit_builder_get_current_page();
    }

    $section = exhibit_builder_get_exhibit_section_by_id($page->section_id);
    $exhibit = exhibit_builder_get_exhibit_by_id($section->exhibit_id);
    
    if(!isset($props['class'])) {
        $props['class'] = 'previous-page';
    }
    
    // If page object exists, grab link to previous exhibit page if exists. If it doesn't, grab
    // a link to the last page on the previous exhibit section, if it exists.
    if ($previousPage = $page->previous()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $section, $previousPage);
    } elseif ($previousSection = $section->previous()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $previousSection);
    }      
}

/**
 * Returns whether an exhibit page has an item
 * 
 * @todo Needs optimization (shouldn't return the item object every time it's checked).
 * @param integer $index The index of the page item
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
 * Sets the exhibit pages for loop by the exhibit section
 *
 * @param ExhibitSection|null $exhibitSection If null, it uses the current section
 * @return void
 **/
function set_exhibit_pages_for_loop_by_section($exhibitSection = null) 
{   
    if (!$exhibitSection) {
        $exhibitSection = get_current_section();
    }
        
    if ($exhibitSection) {
        set_exhibit_pages_for_loop($exhibitSection->Pages);
    }
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
function exhibit_page($propertyName, $options=array(), $exhibitPage=null)
{
    if (!$exhibitPage) {
        $exhibitPage = get_current_exhibit_page();
    }
        
	if (property_exists(get_class($exhibitPage), $propertyName)) {
	    return $exhibitPage->$propertyName;
	} else {
	    return null;
	}
}