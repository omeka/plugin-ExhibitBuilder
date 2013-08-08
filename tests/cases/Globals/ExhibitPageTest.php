<?php
/**
 * Tests for exhibit_page function
 */
class ExhibitPageTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether metadata() returns the correct value for an exhibit page.
     *
     * @uses metadata()
     **/
    public function testCanRetrieveCorrectExhibitPageValue()
    {
        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());


        $exhibitPage = $this->helper->createNewExhibitPage($exhibit, null, 'Exhibit Page Title' , 'exhibitpageslug', 1, 'text');
        $this->assertTrue($exhibitPage->exists());

        $this->dispatch('exhibits/show/exhibitslug/exhibitpageslug');

        $exhibitPage = get_current_record('exhibit_page');
        $this->assertTrue($exhibitPage->exists());
        $this->assertEquals('Exhibit Page Title', $exhibitPage->title);
        $this->assertEquals('exhibitpageslug', $exhibitPage->slug);

        // Exhibit Page Title
        $this->assertEquals('Exhibit Page Title', metadata('exhibitPage', 'Title'));

        // Exhibit Page Layout
        $this->assertEquals('text', metadata('exhibitPage', 'Layout'));

        // Exhibit Page Order
        $this->assertEquals(1, metadata('exhibitPage', 'Order'));

        // Exhibit Page Slug
        $this->assertEquals('exhibitpageslug', metadata('exhibitPage', 'Slug'));

    }
}
