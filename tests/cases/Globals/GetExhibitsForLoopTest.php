<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

/**
 * Tests for get_exhibits_for_loop function
 */
class GetExhibitsForLoopTest extends ExhibitBuilder_ViewTestCase 
{
	/**
	 * Tests whether get_exhibits_for_loop returns exhibits that have been get on the view.
	 */
	public function testGetExhibitsForLoop()
	{
		$exhibits = $this->_createExhibitArray();
		
		$this->view->exhibits = $exhibits;
		
		$loopExhibits = get_exhibits_for_loop();
		$this->assertSame($exhibits, $loopExhibits);
		
		$exhibitsCount = 0;
		foreach ($loopExhibits as $exhibit) {
			$this->assertTrue(in_array($exhibit, $exhibits));
			$exhibitsCount++;
		}
	}
}