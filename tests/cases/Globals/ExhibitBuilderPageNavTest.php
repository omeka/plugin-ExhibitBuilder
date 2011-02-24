<?php
/**
 * Tests for exhibit_builder_page_text function
 */
class ExhibitBuilderPageNavTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $this->exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($this->exhibit->exists());

        $this->exhibitSection = $this->helper->createNewExhibitSection($this->exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug', 1);
        $this->assertTrue($this->exhibitSection->exists());

        $maxPageCount = 4;
        for($i = 1; $i <= $maxPageCount; $i++) {
            $exhibitPage = $this->helper->createNewExhibitPage($this->exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
        }

        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug/exhibitpageslug2');

        $this->basePageUrl = public_uri('exhibits/show/exhibitslug/exhibitsectionslug/exhibitpageslug');
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
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '1' . '">Exhibit Page Title 1</a>';
        $html .= '</li>' . "\n";
        
        $html .= '<li class="current">';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '2' . '">Exhibit Page Title 2</a>';
        $html .= '</li>' . "\n";
        
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '3' . '">Exhibit Page Title 3</a>';
        $html .= '</li>' . "\n";
         
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '4' . '">Exhibit Page Title 4</a>';
        $html .= '</li>' . "\n";
        
        $html .= '</ul>' . "\n";
        
        $this->assertEquals($html, exhibit_builder_page_nav($this->exhibitSection, 'Title'));
        $this->assertEquals($html, exhibit_builder_page_nav($this->exhibitSection, 'title'));
    }

    public function testOrderOutput()
    {
        // Test the page nav when the page orders are used as the link texts
        $html = '';
        $html .= '<ul class="exhibit-page-nav">' . "\n";

        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '1' . '">1</a>';
        $html .= '</li>' . "\n";

        $html .= '<li class="current">';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '2' . '">2</a>';
        $html .= '</li>' . "\n";

        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '3' . '">3</a>';
        $html .= '</li>' . "\n";

        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $this->basePageUrl . '4' . '">4</a>';
        $html .= '</li>' . "\n";

        $html .= '</ul>' . "\n";

        $this->assertEquals($html, exhibit_builder_page_nav($this->exhibitSection, 'Order'));
        $this->assertEquals($html, exhibit_builder_page_nav($this->exhibitSection, 'order'));
    }
}
