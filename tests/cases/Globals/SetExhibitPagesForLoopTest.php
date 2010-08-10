<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for set_exhibit_pages_for_loop function
 */
class SetExhibitPagesForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether set_exhibit_pages_for_loop correctly sets exhibit pages on the view.
     */
    public function testSetExhibitPagesForLoop()
    {
        $maxExhibitPageCount = 8;
        $exhibitPages = $this->_createExhibitPageArray($maxExhibitPageCount);

        set_exhibit_pages_for_loop($exhibitPages);
        $this->assertSame($exhibitPages, $this->view->exhibitPages);

        $exhibitPageCount = 0;
        foreach ($this->view->exhibitPages as $exhibitPage) {
            $this->assertTrue(in_array($exhibitPage, $exhibitPages));
            $exhibitPageCount++;
        }
        $this->assertEquals($maxExhibitPageCount, $exhibitPageCount);
    }
}