<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for exhibit_builder_is_current_page function
 */
class ExhibitBuilderIsCurrentPageTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_is_current_page correctly determines which exhibit page is current.
     */
    public function testExhibitBuilderIsCurrentPage()
    {
        $exhibitPageOne = new ExhibitPage;
        $exhibitPageOne->id = 1;
        $exhibitPageTwo = new ExhibitPage;
        $exhibitPageTwo->id = 2;
        $this->assertFalse(exhibit_builder_is_current_page($exhibitPageOne));
        $this->assertFalse(exhibit_builder_is_current_page($exhibitPageTwo));

        $this->view->exhibit_page = $exhibitPageOne;
        $this->assertTrue(exhibit_builder_is_current_page($exhibitPageOne));
        $this->assertFalse(exhibit_builder_is_current_page($exhibitPageTwo));

        $this->view->exhibit_page = $exhibitPageTwo;
        $this->assertFalse(exhibit_builder_is_current_page($exhibitPageOne));
        $this->assertTrue(exhibit_builder_is_current_page($exhibitPageTwo));
    }
}
