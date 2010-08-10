<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for get_exhibit_pages_for_loop function
 */
class GetExhibitPagesForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether get_exhibit_pages_for_loop returns exhibit pages that have been set on the view.
     */
    public function testGetExhibitPagesForLoop()
    {
        $maxExhibitPageCount = 8;
        $exhibitPages = $this->_createExhibitPageArray($maxExhibitPageCount);

        $this->view->exhibitPages = $exhibitPages;

        $loopExhibitPages = get_exhibit_pages_for_loop();
        $this->assertSame($exhibitPages, $loopExhibitPages);

        $exhibitPageCount = 0;
        foreach ($loopExhibitPages as $exhibitPage) {
            $this->assertTrue(in_array($exhibitPage, $exhibitPages));
            $exhibitPageCount++;
        }
        $this->assertEquals($maxExhibitPageCount, $exhibitPageCount);
    }
}