<?php

/**
 * Tests for set_exhibit_pages_for_loop_by_section function
 */
class SetExhibitPagesForLoopBySectionTest extends ExhibitBuilder_TestCase 
{
	/**
	 * Tests whether set_exhibit_pages_for_loop_by_section correctly sets exhibit pages on the view when the exhibit section is specified.
	 */
	public function testSetExhibitPagesForLoopBySectionWhenSectionIsSpecified()
    {
		$exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());

        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($exhibitSection->exists());
        
        $maxExhibitPageCount = 6;
        $exhibitPageSlugs[] = array();
        for($i = 1; $i <= $maxExhibitPageCount; $i++) {
            $exhibitPage = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
            $exhibitPageSlugs[] = $exhibitPage->slug;
        }
        
        set_exhibit_pages_for_loop_by_section($exhibitSection);
        $exhibitPageCount = 0;
        foreach ($this->view->exhibitPages as $exhibitPage) {
            $this->assertTrue(in_array($exhibitPage->slug, $exhibitPageSlugs));
            $exhibitPageCount++;
        }
        $this->assertEquals($maxExhibitPageCount, $exhibitPageCount);
    }
    
    /**
	 * Tests whether set_exhibit_pages_for_loop_by_section correctly sets exhibit pages on the view when the exhibit section is not specified.
	 */
	public function testSetExhibitPagesForLoopBySectionWhenSectionIsNotSpecified()
    {
		$exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());

        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($exhibitSection->exists());
        
        $maxExhibitPageCount = 6;
        $exhibitPageSlugs[] = array();
        for($i = 1; $i <= $maxExhibitPageCount; $i++) {
            $exhibitPage = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
            $exhibitPageSlugs[] = $exhibitPage->slug;
        }
		
		exhibit_builder_set_current_exhibit($exhibit);
		exhibit_builder_set_current_section($exhibitSection);
        
        // Make sure it uses the current exhibit by default
        set_exhibit_pages_for_loop_by_section();
        $exhibitPageCount = 0;
        foreach ($this->view->exhibitPages as $exhibitPage) {
            $this->assertTrue(in_array($exhibitPage->slug, $exhibitPageSlugs));
            $exhibitPageCount++;
        }
        $this->assertEquals($maxExhibitPageCount, $exhibitPageCount);
    }
}