<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for loop_exhibits function
 */
class LoopExhibitsTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether loop_exhibits loops over exhibits set on the view.
     */
    public function testLoopExhibits()
    {
        $maxExhibitCount = 10;
        $exhibits = $this->_createExhibitArray($maxExhibitCount);
        $this->view->exhibits = $exhibits;

        $exhibitsCount = 0;
        while (loop_exhibits()) {
                $exhibit = $this->view->exhibit;
                $this->assertTrue(in_array($exhibit, $exhibits));
                $exhibitsCount++;
        }
        $this->assertEquals($maxExhibitCount, $exhibitsCount);
    }
}