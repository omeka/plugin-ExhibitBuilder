<?php
/**
 * Tests for exhibit_builder_page_text function
 */
class ExhibitBuilderPageNavTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit_builder_page_nav() returns the correct page navigation html
     *
     * @uses exhibit_builder_page_nav()
     **/
    public function testExhibitBuilderPageNav() 
    {
        $exhibit = $this->_createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $exhibitSection = $this->_createNewExhibitSection($exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug', 1);
        $this->assertTrue($exhibitSection->exists());
        
        $maxPageCount = 4;
        for($i = 1; $i <= $maxPageCount; $i++) {
            $exhibitPage = $this->_createNewExhibitPage($exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
        }
        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug/exhibitpageslug2');
        
        $basePageUrl = public_uri('exhibits/show/exhibitslug/exhibitsectionslug/exhibitpageslug');
        
        // Test the page nav when the page titles are used as the link texts        
        $html = '';
        $html .= '<ul class="exhibit-page-nav">' . "\n";
        
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '1' . '">Exhibit Page Title 1</a>';
        $html .= '</li>' . "\n";
        
        $html .= '<li class="current">';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '2' . '">Exhibit Page Title 2</a>';
        $html .= '</li>' . "\n";
        
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '3' . '">Exhibit Page Title 3</a>';
        $html .= '</li>' . "\n";
         
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '4' . '">Exhibit Page Title 4</a>';
        $html .= '</li>' . "\n";
        
        $html .= '</ul>' . "\n";
        
        $this->assertEquals($html, exhibit_builder_page_nav($exhibitSection, 'Title'));
        $this->assertEquals($html, exhibit_builder_page_nav($exhibitSection, 'title'));
        
        // Test the page nav when the page orders are used as the link texts        
        $html = '';
        $html .= '<ul class="exhibit-page-nav">' . "\n";
        
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '1' . '">1</a>';
        $html .= '</li>' . "\n";
        
        $html .= '<li class="current">';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '2' . '">2</a>';
        $html .= '</li>' . "\n";
        
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '3' . '">3</a>';
        $html .= '</li>' . "\n";
         
        $html .= '<li>';
        $html .= '<a class="exhibit-page-title" href="' . $basePageUrl . '4' . '">4</a>';
        $html .= '</li>' . "\n";
        
        $html .= '</ul>' . "\n";
        
        $this->assertEquals($html, exhibit_builder_page_nav($exhibitSection, 'Order'));
        $this->assertEquals($html, exhibit_builder_page_nav($exhibitSection, 'order'));
    }
}