<?php
/**
 * Tests for total_exhibit_sections function
 */
class TotalExhibitSectionsTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether total_exhibit_sections returns correct count.
     *
     * @uses total_exhibit_sections
     **/
    public function testCanGetExhibitCount() 
    {
        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $maxExhibitSectionCount = 8;
        for($i = 1; $i <= $maxExhibitSectionCount; $i++) {
            $exhibitSection = $this->helper->createNewExhibitSection($exhibit, 'Exhibit Section Title ' . $i, 'Exhibit Section Description ' . $i, 'exhibitsectionslug' . $i);
            $this->assertTrue($exhibitSection->exists());
        }
      
        $this->assertEquals($maxExhibitSectionCount, total_exhibit_sections()); 
    }
}