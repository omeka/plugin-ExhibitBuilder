<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for exhibit_builder_is_current_exhibit function
 */
class ExhibitBuilderIsCurrentExhibitTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_is_current_exhibit correctly determines which exhibit is current.
     */
    public function testExhibitBuilderIsCurrentExhibit()
    {
        $exhibitOne = new Exhibit;
        $exhibitOne->id = 1;
        $exhibitTwo = new Exhibit;
        $exhibitTwo->id = 2;
        $this->assertFalse(exhibit_builder_is_current_exhibit($exhibitOne));
        $this->assertFalse(exhibit_builder_is_current_exhibit($exhibitTwo));

        $this->view->exhibit = $exhibitOne;
        $this->assertTrue(exhibit_builder_is_current_exhibit($exhibitOne));
        $this->assertFalse(exhibit_builder_is_current_exhibit($exhibitTwo));

        $this->view->exhibit = $exhibitTwo;
        $this->assertFalse(exhibit_builder_is_current_exhibit($exhibitOne));
        $this->assertTrue(exhibit_builder_is_current_exhibit($exhibitTwo));
    }
}