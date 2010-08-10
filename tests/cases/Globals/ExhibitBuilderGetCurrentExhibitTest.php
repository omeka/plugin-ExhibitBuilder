<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for exhibit_builder_get_current_exhibit function
 */
class ExhibitBuilderGetCurrentExhibitTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_get_current_exhibit correctly returns an exhibit from the view.
     */
    public function testExhibitBuilderGetCurrentExhibit()
    {
        $this->view->exhibit = new Exhibit;
        $exhibit = exhibit_builder_get_current_exhibit();
        $this->assertSame($this->view->exhibit, $exhibit);
        $exhibit->title = 'test';
        // Ensures that the view is actually referencing the same object.
        $this->assertSame($this->view->exhibit, $exhibit);
    }

    /**
     * Tests whether exhibit_builder_get_current_exhibit returns null when no exhibit is set on the view.
     */
    public function testExhibitBuilderGetCurrentExhibitNull()
    {
        $exhibit = exhibit_builder_get_current_exhibit();
        $this->assertNull($exhibit);
    }
}