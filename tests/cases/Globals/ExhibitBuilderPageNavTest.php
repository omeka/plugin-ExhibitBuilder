<?php
/**
 * Tests for exhibit_builder_page_nav function
 */
class ExhibitBuilderPageNavTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $this->exhibit = $this->helper->createNewExhibit(
            true, false, 'Exhibit Title', 'Exhibit Description',
            'Exhibit Credits', 'exhibit');
        $this->assertTrue($this->exhibit->exists());

        $maxPageCount = 3;
        $parentPage = null;
        for($i = 1; $i <= $maxPageCount; $i++) {
            $exhibitPage = $this->helper->createNewExhibitPage(
                $this->exhibit, $parentPage, 'Exhibit Page Title ' . $i,
                'page' . $i, 1, 'text');
            $this->assertTrue($exhibitPage->exists());
            $parentPage = $exhibitPage;
        }

        $this->dispatch('exhibits/show/exhibit/page1/page2/page3');

        $this->basePageUrl = public_url('exhibits/show/exhibit');
    }


    /**
     * Tests whether exhibit_builder_page_nav() returns the correct page navigation html
     *
     * @uses exhibit_builder_page_nav()
     **/
    public function testTitleOutput()
    {
        // Test the page nav when the page titles are used as the link texts
        $html = '';
        $html .= '<ul class="exhibit-page-nav">' . "\n";

        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl  . '">Exhibit Title</a>';
        $html .= '</li>' . "\n";

        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl  . '/page1">Exhibit Page Title 1</a>';
        $html .= '</li>' . "\n";

        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl  . '/page1/page2">Exhibit Page Title 2</a>';
        $html .= '</li>' . "\n";

        $html .= '<li class="current">';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '/page1/page2/page3">Exhibit Page Title 3</a>';
        $html .= '</li>' . "\n";

        $html .= '</ul>' . "\n";

        $this->assertEquals($html, exhibit_builder_page_nav($this->exhibitPage));
        $this->assertEquals($html, exhibit_builder_page_nav($this->exhibitPage));
    }
}
