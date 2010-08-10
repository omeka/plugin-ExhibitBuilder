<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for exhibit_builder_get_current_section function
 */
class ExhibitBuilderGetCurrentSectionTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_get_current_section correctly returns an exhibit section from the view.
     */
    public function testExhibitBuilderGetCurrentSection()
    {
        $this->view->exhibitSection = new ExhibitSection;
        $exhibitSection = exhibit_builder_get_current_section();
        $this->assertSame($this->view->exhibitSection, $exhibitSection);
        $exhibitSection->title = 'test';
        // Ensures that the view is actually referencing the same object.
        $this->assertSame($this->view->exhibitSection, $exhibitSection);
    }

    /**
     * Tests whether exhibit_builder_get_current_section returns null when no exhibit section is set on the view.
     */
    public function testExhibitBuilderGetCurrentSectionNull()
    {
        $exhibitSection = exhibit_builder_get_current_section();
        $this->assertNull($exhibitSection);
    }
}