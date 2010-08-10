<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for has_exhibit_sections_for_loop function
 */
class HasExhibitSectionsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether has_exhibit_sections_for_loop correctly detects whether exhibit sections are set.
     */
    public function testHasExhibitSectionsForLoop()
    {
        $this->assertFalse(has_exhibit_sections_for_loop());
        $this->view->exhibitSections = array();
        $this->assertFalse(has_exhibit_sections_for_loop());
        $maxExhibitSectionCount = 6;
        $this->view->exhibitSections = $this->_createExhibitSectionArray($maxExhibitSectionCount);
        $this->assertTrue(has_exhibit_sections_for_loop());
    }
}