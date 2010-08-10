<?php
/**
 * Tests for total_exhibit_pages function
 */
class TotalExhibitPagesTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether total_exhibit_pages returns correct count.
     *
     * @uses total_exhibit_pages
     */
    public function testCanGetExhibitCount() 
    {
        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $exhibitSection = $this->helper->createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($exhibitSection->exists());
        
        $maxExhibitPageCount = 5;
        for($i = 1; $i <= $maxExhibitPageCount; $i++) {
            $exhibitPage = $this->helper->createNewExhibitPage($exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
        }
      
        $this->assertEquals($maxExhibitPageCount, total_exhibit_pages()); 
    }
}