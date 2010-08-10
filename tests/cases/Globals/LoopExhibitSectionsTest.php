<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for loop_exhibit_sections function
 */
class LoopExhibitSectionsTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether loop_exhibit_sections loops over exhibit sections set on the view.
     */
    public function testLoopExhibitSections()
    {
        $maxExhibitSectionCount = 7;
        $exhibitSections = $this->_createExhibitSectionArray($maxExhibitSectionCount);
        $this->view->exhibitSections = $exhibitSections;

        $exhibitSectionCount = 0;
        while (loop_exhibit_sections()) {
            $exhibitSection = $this->view->exhibitSection;
            $this->assertTrue(in_array($exhibitSection, $exhibitSections));
            $exhibitSectionCount++;
        }
        $this->assertEquals($maxExhibitSectionCount, $exhibitSectionCount);
    }
}