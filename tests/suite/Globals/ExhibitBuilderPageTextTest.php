<?php
/**
 * Tests for exhibit_builder_page_text function
 */
class ExhibitBuilderPageTextTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());

        $exhibitPage = $this->helper->createNewExhibitPage($exhibit, null, 'Exhibit Page Title' , 'exhibitpageslug', 1, 'text');
        $this->assertTrue($exhibitPage->exists());

        $this->maxExhibitPageEntries = 7;
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $exhibitPageEntry = $this->helper->createNewExhibitPageEntry($exhibitPage, 'Exhibit Page Entry '.$i, $i, null);
            $this->assertTrue($exhibitPageEntry->exists());
        }

        $this->dispatch('exhibits/show/exhibitslug/exhibitpageslug');
    }

    /**
     * Tests whether exhibit_builder_page_text() returns the correct value when the exhibit page is specified
     *
     * @uses exhibit_builder_page_text()
     */
    public function testExhibitBuilderPageTextWhenExhibitPageIsSpecified()
    {
        $exhibitPage = get_current_record('exhibit_page');
        $this->assertTrue($exhibitPage->exists());

        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $this->assertEquals('Exhibit Page Entry '.$i, exhibit_builder_page_text($i, $exhibitPage));
        }
    }

    /**
     * Tests whether exhibit_builder_page_text() returns the correct value when the exhibit page is not specified
     *
     * @uses exhibit_builder_page_text()
     */
    public function testExhibitBuilderPageTextWhenExhibitPageIsNotSpecified()
    {
        $exhibitPage = get_current_record('exhibit_page');
        $this->assertTrue($exhibitPage->exists());

        set_current_record('exhibit_page', $exhibitPage);
        for($i = 1; $i <= $this->maxExhibitPageEntries; $i++) {
            $this->assertEquals('Exhibit Page Entry '.$i, exhibit_builder_page_text($i));
        }
    }
}
