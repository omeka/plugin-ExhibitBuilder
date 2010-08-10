<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for has_exhibit_pages_for_loop function
 */
class HasExhibitPagesForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether has_exhibit_pages_for_loop correctly detects whether exhibit pages are set.
     */
    public function testHasExhibitPagesForLoop()
    {
        $this->assertFalse(has_exhibit_pages_for_loop());
        $this->view->exhibitPages = array();
        $this->assertFalse(has_exhibit_pages_for_loop());
        $maxExhibitPageCount = 6;
        $this->view->exhibitPages = $this->_createExhibitPageArray($maxExhibitPageCount);
        $this->assertTrue(has_exhibit_pages_for_loop());
    }
}