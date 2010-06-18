<?php
/**
 * Tests for has_exhibit_sections function
 */
class HasExhibitSectionsTest extends ExhibitBuilder_TestCase 
{
    /**
    * Tests whether has_exhibit_sections returns true when exhibit sections exist and false when they don't exist.
     *
     * @uses has_exhibit_sections
     **/
    public function testHasExhibitSections() 
    {
		$exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());

        $this->assertFalse(has_exhibit_sections(), 'Should not have exhibit sections!');

        $maxExhibitSectionCount = 4;
        for($i = 1; $i <= $maxExhibitSectionCount; $i++) {
            $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title ' . $i, 'Exhibit Section Description ' . $i, 'exhibitsectionslug' . $i);
            $this->assertTrue($exhibitSection->exists());
        }

        $this->assertTrue(has_exhibit_sections(), 'Should have exhibit sections!');
    }
}