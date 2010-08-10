<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for set_exhibit_sections_for_loop function
 */
class SetExhibitSectionsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether set_exhibit_sections_for_loop correctly sets exhibit sections on the view.
     */
    public function testSetExhibitSectionsForLoop()
    {
        $maxExhibitSectionCount = 8;
        $exhibitSections = $this->_createExhibitSectionArray($maxExhibitSectionCount);

        set_exhibit_sections_for_loop($exhibitSections);
        $this->assertSame($exhibitSections, $this->view->exhibitSections);

        $exhibitSectionCount = 0;
        foreach ($this->view->exhibitSections as $exhibitSection) {
                $this->assertTrue(in_array($exhibitSection, $exhibitSections));
                $exhibitSectionCount++;
        }
        $this->assertEquals($maxExhibitSectionCount, $exhibitSectionCount);
    }
}