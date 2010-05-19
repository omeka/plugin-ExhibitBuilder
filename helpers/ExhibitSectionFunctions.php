<?php

/**
 * Returns the current exhibit section.
 *
 * @return ExhibitSection|null
 **/
function exhibit_builder_get_current_section()
{
    if (Zend_Registry::isRegistered('exhibit_builder_section')) {
        return Zend_Registry::get('exhibit_builder_section');
    }
    return false;
}

/**
 * Sets the current exhibit section.
 *
 * @param ExhibitSection|null $section
 * @return void
 **/
function exhibit_builder_set_current_section($section=null)
{
    Zend_Registry::set('exhibit_builder_section', $section);
}

/**
 * Returns whether an exhibit section is the current exhibit section.
 *
 * @param ExhibitSection|null $section
 * @return boolean
 **/
function exhibit_builder_is_current_section($section)
{
    $currentSection = exhibit_builder_get_current_section();
    return ($section === $currentSection || ($section && $currentSection && $section->id == $currentSection->id));
}

/**
 * Returns whether the exhibit section has pages.
 *
 * @param ExhibitSection $section
 * @return boolean
 **/
function exhibit_builder_section_has_pages($section) 
{
    return $section->hasPages();
}

/**
 * Returns an exhibit section by id
 *
 * @param $sectionId The id of the exhibit section
 * @return ExhibitSection
 **/
function exhibit_builder_get_exhibit_section_by_id($sectionId) 
{
    return get_db()->getTable('ExhibitSection')->find($sectionId);
}

/**
 * Returns the HTML code of the exhibit section navigation
 *
 * @param Exhibit|null $exhibit If null, will use the current exhibit.
 * @return string
 **/
function exhibit_builder_section_nav($exhibit=null)
{
    if (!$exhibit) {
        if (!($exhibit = exhibit_builder_get_current_exhibit())) {
            return;
        }    
    }
    $html = '<ul class="exhibit-section-nav">';
    foreach ($exhibit->Sections as $key => $section) {      
        $html .= '<li' . (exhibit_builder_is_current_section($section) ? ' class="current"' : ''). '><a class="exhibit-section-title" href="' . html_escape(exhibit_builder_exhibit_uri($exhibit, $section)) . '">' . html_escape($section->title) . '</a></li>';
    }
    $html .= '</ul>';
    return $html;
}

/**
 * Returns the HTML for a nested navigation for exhibit sections and pages
 *
 * @param Exhibit|null $exhibit If null, will use the current exhibit
 * @param boolean $showAllPages
 * @return string
 **/
function exhibit_builder_nested_nav($exhibit = null, $showAllPages = false)
{
    if (!$exhibit) {
        if (!($exhibit = exhibit_builder_get_current_exhibit())) {
            return;
        }    
    }
    $html = '<ul class="exhibit-section-nav">';
    foreach ($exhibit->Sections as $section) {
        $html .= '<li class="exhibit-nested-section' . (exhibit_builder_is_current_section($section) ? ' current' : '') . '"><a class="exhibit-section-title" href="' . html_escape(exhibit_builder_exhibit_uri($exhibit, $section)) . '">' . html_escape($section->title) . '</a>';
        if ($showAllPages || exhibit_builder_is_current_section($section)) {
            $html .= exhibit_builder_page_nav($section);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}