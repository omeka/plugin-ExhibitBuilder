<?php
/**
 * Tests for total_exhibit_pages function
 */
class TotalExhibitPagesTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether total_exhibit_pages returns correct count.
     *
     * @uses total_exhibit_pages
     **/
    public function testCanGetExhibitCount() 
    {
		$exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($exhibitSection->exists());
        
        $maxExhibitPageCount = 5;
        for($i = 1; $i <= $maxExhibitPageCount; $i++) {
            $exhibitPage = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
        }
      
        $this->assertEquals($maxExhibitPageCount, total_exhibit_pages()); 
    }
}