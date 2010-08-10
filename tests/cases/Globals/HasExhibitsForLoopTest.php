<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for has_exhibits_for_loop function
 */
class HasExhibitsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether has_exhibits_for_loop correctly detects whether exhibits are set.
     */
    public function testHasExhibitsForLoop()
    {
        $this->assertFalse(has_exhibits_for_loop());
        $this->view->exhibits = array();
        $this->assertFalse(has_exhibits_for_loop());
        $maxExhibitCount = 6;
        $this->view->exhibits = $this->_createExhibitArray($maxExhibitCount);
        $this->assertTrue(has_exhibits_for_loop());
    }
}