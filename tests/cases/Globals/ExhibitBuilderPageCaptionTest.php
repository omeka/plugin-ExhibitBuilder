<?php
/**
 * Tests for exhibit_builder_page_caption function
 */
class ExhibitBuilderPageCaptionTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());

        $exhibitSection = $this->helper->createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug', 1);
        $this->assertTrue($exhibitSection->exists());

        $exhibitPage = $this->helper->createNewExhibitPage($exhibitSection, 'Exhibit Page Title' , 'exhibitpageslug', 1, 'text-image-left');
        $this->assertTrue($exhibitPage->exists());

        $this->maxExhibitPageEntries = 7;
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $exhibitPageEntry = $this->helper->createNewExhibitPageEntry($exhibitPage, null, $i, null, 'Exhibit Page Caption '.$i);
            $this->assertTrue($exhibitPageEntry->exists());
        }

        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug');
    }

    /**
     * Tests whether exhibit_builder_page_caption() returns the correct value when the exhibit page is specified
     *
     * @uses exhibit_builder_page_caption(), get_current_exhibit_section()
     **/
    public function testExhibitBuilderPageTextWhenExhibitPageIsSpecified() 
    {
        $exhibitSection = get_current_exhibit_section();

        $exhibitPages = $exhibitSection->getPages();
        $this->assertEquals(1, count($exhibitPages));
        
        $exhibitPage = $exhibitPages[0];
        $this->assertTrue($exhibitPage->exists());
        
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $this->assertEquals('Exhibit Page Caption '.$i, exhibit_builder_page_caption($i, $exhibitPage));
        }
    }
    
    /**
     * Tests whether exhibit_builder_page_text() returns the correct value when the exhibit page is not specified
     *
     * @uses exhibit_builder_page_text(), get_current_exhibit_section(), set_current_exhibit_page
     **/
    public function testExhibitBuilderPageTextWhenExhibitPageIsNotSpecified() 
    {
        $exhibitSection = get_current_exhibit_section();
         
        $exhibitPages = $exhibitSection->getPages();
        $this->assertEquals(1, count($exhibitPages));
        
        $exhibitPage = $exhibitPages[0];
        $this->assertTrue($exhibitPage->exists());
        
        set_current_exhibit_page($exhibitPage);
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $this->assertEquals('Exhibit Page Caption '.$i, exhibit_builder_page_caption($i));
        }
    }
}
