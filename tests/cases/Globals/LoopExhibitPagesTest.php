<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitPageFunctions.php';

/**
 * Tests for loop_exhibit_pages function
 */
class LoopExhibitPagesTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether loop_exhibit_pages loops over exhibit pages set on the view.
     */
    public function testLoopExhibitPages()
    {
        $maxExhibitPageCount = 7;
        $exhibitPages = $this->_createExhibitPageArray($maxExhibitPageCount);
        $this->view->exhibitPages = $exhibitPages;

        $exhibitPageCount = 0;
        while (loop_exhibit_pages()) {
            $exhibitPage = $this->view->exhibitPage;
            $this->assertTrue(in_array($exhibitPage, $exhibitPages));
            $exhibitPageCount++;
        }
        $this->assertEquals($maxExhibitPageCount, $exhibitPageCount);
    }
}