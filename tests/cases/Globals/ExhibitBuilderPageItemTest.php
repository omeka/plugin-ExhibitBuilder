<?php
/**
 * Tests for exhibit_builder_page_item function
 */
class ExhibitBuilderPageItemTest extends Omeka_Test_AppTestCase
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

        $exhibitPage = $this->helper->createNewExhibitPage($exhibitSection, 'Exhibit Page Title' , 'exhibitpageslug', 1, 'gallery-thumbnails');
        $this->assertTrue($exhibitPage->exists());

        $this->maxExhibitPageEntries = 7;
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $item = $this->helper->createNewItem(true, 'Item Title ' . $i);
            $exhibitPageEntry = $this->helper->createNewExhibitPageEntry($exhibitPage, 'Exhibit Page Entry '.$i, $i, $item);
            $this->assertTrue($exhibitPageEntry->exists());
        }
    }

    /**
     * Tests whether exhibit_builder_page_item() returns the correct item when the exhibit page is specified
     *
     * @uses exhibit_builder_page_text(), get_current_exhibit_section()
     **/
    public function testExhibitBuilderPageItemWhenExhibitPageIsSpecified() 
    {   
        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug');

        $exhibitSection = get_current_exhibit_section();

        $exhibitPages = $exhibitSection->getPages();
        $this->assertEquals(1, count($exhibitPages));
        
        $exhibitPage = $exhibitPages[0];
        $this->assertTrue($exhibitPage->exists());
        
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $item = exhibit_builder_page_item($i, $exhibitPage);
            $this->assertTrue($item->exists());
            $itemTitle = item('Dublin Core', 'Title', array(), $item);
            $this->assertEquals('Item Title '. $i, $itemTitle);
        }
    }
    
    /**
     * Tests whether exhibit_builder_page_item() returns the correct item when the exhibit page is not specified
     *
     * @uses exhibit_builder_page_text(), get_current_exhibit_section(), set_current_exhibit_page
     **/
    public function testExhibitBuilderPageItemWhenExhibitPageIsNotSpecified() 
    {
        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug');

        $exhibitSection = get_current_exhibit_section();

        $exhibitPages = $exhibitSection->getPages();
        $this->assertEquals(1, count($exhibitPages));
        
        $exhibitPage = $exhibitPages[0];
        $this->assertTrue($exhibitPage->exists());
         
        set_current_exhibit_page($exhibitPage);
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $item = exhibit_builder_page_item($i);
            $this->assertTrue($item->exists());
            $itemTitle = item('Dublin Core', 'Title', array(), $item);
            $this->assertEquals('Item Title '. $i, $itemTitle);
        }
    }
}
