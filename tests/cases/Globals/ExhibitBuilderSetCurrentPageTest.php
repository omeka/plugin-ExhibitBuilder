<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for exhibit_builder_set_current_page function
 */
class ExhibitBuilderSetCurrentPageTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_set_current_page correctly sets an exhibit page on the view.
     */
    public function testExhibitBuilderSetCurrentPage()
    {
        $exhibitPage = new ExhibitPage;
        exhibit_builder_set_current_page($exhibitPage);
        $this->assertSame($exhibitPage, $this->view->exhibitPage);
        $exhibitPage->title = 'test';
        // Ensures that the view is actually referencing the same object.
        $this->assertSame($exhibitPage, $this->view->exhibitPage);
    }
}