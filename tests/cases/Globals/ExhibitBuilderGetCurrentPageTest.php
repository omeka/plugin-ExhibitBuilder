<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for exhibit_builder_get_current_page function
 */
class ExhibitBuilderGetCurrentPageTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_get_current_page correctly returns an exhibit page from the view.
     */
    public function testExhibitBuilderGetCurrentPage()
    {
        $this->view->exhibitPage = new ExhibitPage;
        $exhibitPage = exhibit_builder_get_current_page();
        $this->assertSame($this->view->exhibitPage, $exhibitPage);
        $exhibitPage->title = 'test';
        // Ensures that the view is actually referencing the same object.
        $this->assertSame($this->view->exhibitPage, $exhibitPage);
    }

    /**
     * Tests whether exhibit_builder_get_current_page returns null when no exhibit page is set on the view.
     */
    public function testExhibitBuilderGetCurrentPageNull()
    {
        $exhibitPage = exhibit_builder_get_current_page();
        $this->assertNull($exhibitPage);
    }
}