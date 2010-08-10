<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for set_exhibits_for_loop function
 */
class SetExhibitsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether set_exhibits_for_loop correctly sets exhibits on the view.
     */
    public function testSetExhibitsForLoop()
    {
        $maxExhibitCount = 8;
        $exhibits = $this->_createExhibitArray($maxExhibitCount);

        set_exhibits_for_loop($exhibits);
        $this->assertSame($exhibits, $this->view->exhibits);

        $exhibitCount = 0;
        foreach ($this->view->exhibits as $exhibit) {
                $this->assertTrue(in_array($exhibit, $exhibits));
                $exhibitCount++;
        }

        $this->assertEquals($maxExhibitCount, $exhibitCount);
    }
}