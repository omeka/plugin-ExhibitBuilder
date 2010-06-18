<?php
/**
 * Tests for exhibit_builder_get_exhibits function
 */
class ExhibitBuilderSectionHasPagesTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit_builder_section_has_pages has no pages when the current section does not have any pages
     *
     * @uses exhibit_builder_section_has_pages, exhibit_builder_set_current_section, exhibit_builder_get_current_section
     **/
    public function testExhibtBuilderSectionHasPagesWhenSectionHasNoPages() 
    {
        $exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($exhibitSection->exists());
        
        $this->assertFalse(exhibit_builder_section_has_pages($exhibitSection));
         
        // Make sure the current section works        
        exhibit_builder_set_current_section($exhibitSection);
        $this->assertSame($exhibitSection, exhibit_builder_get_current_section());
        $this->assertFalse(exhibit_builder_section_has_pages());
    }
    
    /**
     * Tests whether exhibit_builder_section_has_pages has pages when the current section does have pages
     *
     * @uses exhibit_builder_section_has_pages
     **/
    public function testExhibtBuilderSectionHasPagesWhenSectionHasPages() 
    {
        $exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($exhibitSection->exists());
        
        $exhibitPage = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title', 'exhibitpageslug', 1);
        $this->assertTrue($exhibitPage->exists());
        
        $this->assertTrue(exhibit_builder_section_has_pages($exhibitSection));
        
        // Make sure the current section works        
        exhibit_builder_set_current_section($exhibitSection);
        $this->assertSame($exhibitSection, exhibit_builder_get_current_section());
        $this->assertTrue(exhibit_builder_section_has_pages());
        
        // See if multiple pages works
        $exhibitPage2 = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title 2', 'exhibitpageslug2', 2);
        $this->assertTrue($exhibitPage2->exists());
        
        $this->assertTrue(exhibit_builder_section_has_pages($exhibitSection));
        
        // Make sur ethe current section works
        $this->assertTrue(exhibit_builder_section_has_pages());
    }
}