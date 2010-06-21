<?php
/**
 * Tests for exhibit_page function
 */
class ExhibitPageTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit_page() returns the correct value.
     *
     * @uses exhibit_page()
     **/
    public function testCanRetrieveCorrectExhibitPageValue() 
    {
        $exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug', 1);
        $this->assertTrue($exhibitSection->exists());
            
        $exhibitPage = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title' , 'exhibitpageslug', 1, 'text');
        $this->assertTrue($exhibitPage->exists());
        
        $maxExhibitPageEntries = 7;
        for($i = 1; $i <= $maxExhibitPageEntries; $i++) {
            $exhibitPageEntry = $this->_createNewExhibitPageEntry($exhibitPage, 'Exhibit Page Entry', $i, null);
            $this->assertTrue($exhibitPageEntry->exists());
        }
        
        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug');

        $exhibitPage = get_current_exhibit_page();
        $this->assertTrue($exhibitPage->exists());
        $this->assertEquals('Exhibit Page Title', $exhibitPage->title);
        $this->assertEquals('exhibitpageslug', $exhibitPage->slug);

        // Exhibit Page Title
        $this->assertEquals('Exhibit Page Title', exhibit_page('Title'));

        // Exhibit Page Layout
        $this->assertEquals('text', exhibit_page('Layout'));

        // Exhibit Page Order
        $this->assertEquals(1, exhibit_page('Order'));

        // Exhibit Page Slug
        $this->assertEquals('exhibitpageslug', exhibit_page('Slug'));
        
        // Exhibit Page Section Id
        $this->assertEquals($exhibitSection->id, exhibit_page('Section Id'));
    }
}