<?php
/**
 * Tests for has_exhibit_pages function
 */
class HasExhibitPagesTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether has_exhibit_pages returns true when exhibit pages exist and false when they don't exist.
     *
     * @uses has_exhibit_pages
     */
    public function testHasExhibitPages() 
    {
        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());

        $exhibitSection = $this->helper->createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug', 1);
        $this->assertTrue($exhibitSection->exists());

        $this->assertFalse(has_exhibit_pages(), 'Should not have exhibit pages!');

        $maxExhibitPageCount = 4;
        for($i = 1; $i <= $maxExhibitPageCount; $i++) {
            $exhibitPage = $this->helper->createNewExhibitPage($exhibitSection, 'Exhibit Page Title' . $i , 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
        }

        $this->assertTrue(has_exhibit_pages(), 'Should have exhibit pages!');
    }
}