<?php
/**
 * Tests for total_exhibit_sections function
 */
class TotalExhibitSectionsTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether total_exhibit_sections returns correct count.
     *
     * @uses total_exhibit_sections
     **/
    public function testCanGetExhibitCount() 
    {
		$exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $maxExhibitSectionCount = 8;
        for($i = 1; $i <= $maxExhibitSectionCount; $i++) {
            $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title ' . $i, 'Exhibit Section Description ' . $i, 'exhibitsectionslug' . $i);
            $this->assertTrue($exhibitSection->exists());
        }
      
        $this->assertEquals($maxExhibitSectionCount, total_exhibit_sections()); 
    }
}