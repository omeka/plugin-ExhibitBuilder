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
    
    if (count($exhibitPage->ExhibitPageEntry) < $exhibitPageEntryIndex) {
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
 * @param ExhibitSection|null $exhibitSection If null, will use the current exhibit section
 * @param string $linkTextType The type of page information should be used for the link text.  
 * If 'order', it uses the page order as the link text.  
 * If 'title' or any other value, it uses the page title as the link text.
 * @return string
 **/
function exhibit_builder_page_nav($exhibitSection = null, $linkTextType = 'title')
{
    $linkTextType = Inflector::underscore($linkTextType);
    if (!$exhibitSection) {
        if (!($exhibitSection = exhibit_builder_get_current_section())) {
            return;
        }
    }
    if ($exhibitSection->hasPages()) {
        $html = '<ul class="exhibit-page-nav">' . "\n";
        foreach ($exhibitSection->Pages as $exhibitPage) {
            switch($linkTextType) {
                case 'order':
                    $linkText = $exhibitPage->order;
                    break;
                case 'title':
                default:
                    $linkText = $exhibitPage->title;
                    break;
                
            }
            $html .= '<li'. (exhibit_builder_is_current_page($exhibitPage) ? ' class="current"' : '').'><a class="exhibit-page-title" href="'. html_escape(exhibit_builder_exhibit_uri($exhibitSection->Exhibit, $exhibitSection, $exhibitPage)) . '">'. html_escape($linkText) .'</a></li>' . "\n";
        }
        $html .= '</ul>' . "\n";
        $html = apply_filters('exhibit_builder_page_nav', $html, $exhibitSection, $linkTextType);
        return $html;
    }
    return false;
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
    
    $exhibitSection = exhibit_builder_get_exhibit_section_by_id($exhibitPage->section_id);
    $exhibit = exhibit_builder_get_exhibit_by_id($exhibitSection->exhibit_id);
    
    if(!isset($props['class'])) {
        $props['class'] = 'next-page';
    }
    
    // if page object exists, grab link to next exhibit page if exists. If it doesn't, grab
    // a link to the first page on the next exhibit section, if it exists.
    if ($nextPage = $exhibitPage->next()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $exhibitSection, $nextPage);
    } elseif ($nextSection = $exhibitSection->next()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $nextSection);
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

    $exhibitSection = exhibit_builder_get_exhibit_section_by_id($exhibitPage->section_id);
    $exhibit = exhibit_builder_get_exhibit_by_id($exhibitSection->exhibit_id);
    
    if(!isset($props['class'])) {
        $props['class'] = 'previous-page';
    }
    
    // If page object exists, grab link to previous exhibit page if exists. If it doesn't, grab
    // a link to the last page on the previous exhibit section, if it exists.
    if ($previousPage = $exhibitPage->previous()) {
        return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $exhibitSection, $previousPage);
    } elseif ($previousSection = $exhibitSection->previous()) {
        if ($pages = $previousSection->Pages) {
            $page = end($pages);
            return exhibit_builder_link_to_exhibit($exhibit, $text, $props, $previousSection, $page);
        }
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
 * Sets the exhibit pages for loop by the exhibit section
 *
 * @param ExhibitSection|null $exhibitSection If null, it uses the current section
 * @return void
 **/
function set_exhibit_pages_for_loop_by_section($exhibitSection = null) 
{   
    if (!$exhibitSection) {
        $exhibitSection = exhibit_builder_get_current_section();
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
