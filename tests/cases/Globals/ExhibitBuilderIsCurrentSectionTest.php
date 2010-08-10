<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for exhibit_builder_is_current_section function
 */
class ExhibitBuilderIsCurrentSectionTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether exhibit_builder_is_current_section correctly determines which exhibit section is current.
     */
    public function testExhibitBuilderIsCurrentSection()
    {
        $exhibitSectionOne = new ExhibitSection;
        $exhibitSectionOne->id = 1;
        $exhibitSectionTwo = new ExhibitSection;
        $exhibitSectionTwo->id = 2;
        $this->assertFalse(exhibit_builder_is_current_section($exhibitSectionOne));
        $this->assertFalse(exhibit_builder_is_current_section($exhibitSectionTwo));

        $this->view->exhibitSection = $exhibitSectionOne;
        $this->assertTrue(exhibit_builder_is_current_section($exhibitSectionOne));
        $this->assertFalse(exhibit_builder_is_current_section($exhibitSectionTwo));

        $this->view->exhibitSection = $exhibitSectionTwo;
        $this->assertFalse(exhibit_builder_is_current_section($exhibitSectionOne));
        $this->assertTrue(exhibit_builder_is_current_section($exhibitSectionTwo));
    }
}