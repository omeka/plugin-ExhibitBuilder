<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for exhibit_builder_set_current_exhibit function
 */
class ExhibitBuilderSetCurrentExhibitTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_set_current_exhibit correctly sets an exhibit on the view.
     */
    public function testExhibitBuilderSetCurrentExhibit()
    {
        $exhibit = new Exhibit;
        exhibit_builder_set_current_exhibit($exhibit);
        $this->assertSame($exhibit, $this->view->exhibit);
        $exhibit->title = 'test';
        // Ensures that the view is actually referencing the same object.
        $this->assertSame($exhibit, $this->view->exhibit);
    }
}