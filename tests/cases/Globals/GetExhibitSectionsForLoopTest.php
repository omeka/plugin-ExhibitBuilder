<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for get_exhibit_sections_for_loop function
 */
class GetExhibitSectionsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether get_exhibit_sections_for_loop returns exhibit sections that have been set on the view.
     */
    public function testGetExhibitSectionsForLoop()
    {
        $maxExhibitSectionCount = 8;
        $exhibitSections = $this->_createExhibitSectionArray($maxExhibitSectionCount);

        $this->view->exhibitSections = $exhibitSections;

        $loopExhibitSections = get_exhibit_sections_for_loop();
        $this->assertSame($exhibitSections, $loopExhibitSections);

        $exhibitSectionCount = 0;
        foreach ($loopExhibitSections as $exhibitSection) {
            $this->assertTrue(in_array($exhibitSection, $exhibitSections));
            $exhibitSectionCount++;
        }
        $this->assertEquals($maxExhibitSectionCount, $exhibitSectionCount);
    }
}