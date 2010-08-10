<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for exhibit_builder_set_current_section function
 */
class ExhibitBuilderSetCurrentSectionTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_set_current_section correctly sets an exhibit section on the view.
     */
    public function testExhibitBuilderSetCurrentSection()
    {
        $exhibitSection = new ExhibitSection;
        exhibit_builder_set_current_section($exhibitSection);
        $this->assertSame($exhibitSection, $this->view->exhibitSection);
        $exhibitSection->title = 'test';
        // Ensures that the view is actually referencing the same object.
        $this->assertSame($exhibitSection, $this->view->exhibitSection);
    }
}