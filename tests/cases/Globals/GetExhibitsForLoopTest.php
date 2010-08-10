<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for get_exhibits_for_loop function
 */
class GetExhibitsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
    /**
     * Tests whether get_exhibits_for_loop returns exhibits that have been set on the view.
     */
    public function testGetExhibitsForLoop()
    {
        $maxExhibitCount = 8;
        $exhibits = $this->_createExhibitArray($maxExhibitCount);

        $this->view->exhibits = $exhibits;

        $loopExhibits = get_exhibits_for_loop();
        $this->assertSame($exhibits, $loopExhibits);

        $exhibitCount = 0;
        foreach ($loopExhibits as $exhibit) {
            $this->assertTrue(in_array($exhibit, $exhibits));
            $exhibitCount++;
        }
        $this->assertEquals($maxExhibitCount, $exhibitCount);
    }
}