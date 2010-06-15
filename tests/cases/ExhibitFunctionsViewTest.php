<?php
require HELPERS;
require EXHIBIT_BUILDER_DIR . '/models/Exhibit.php';
require EXHIBIT_BUILDER_DIR . '/helpers/ExhibitFunctions.php';

class ExhibitBuilder_ExhibitFunctionsViewTest extends PHPUnit_Framework_TestCase 
{
	private $view;
	
	public function setUp()
	{
		$this->view = new Omeka_View;
		Zend_Registry::set('view', $this->view);
	}
	
	/**
	 * Tests whether get_current_exhibit correctly returns an exhibit from the view.
	 */
	public function testGetCurrentExhibit()
	{
		$this->view->exhibit = new Exhibit;
		$exhibit = exhibit_builder_get_current_exhibit();
		$this->assertSame($this->view->exhibit, $exhibit);
		$exhibit->title = 'test';
		// Ensures that the view is actually referencing the same object.
		$this->assertSame($this->view->exhibit, $exhibit);
	}
	
	/**
	 * Tests whether get_current_exhibit returns null when no exhibit is set on the view.
	 */
	public function testGetCurrentExhibitNull()
	{
		$exhibit = exhibit_builder_get_current_exhibit();
		$this->assertNull($exhibit);
	}
	
	/**
	 * Tests whether set_current_exhibit correctly sets an exhibit on the view.
	 */
	public function testSetCurrentExhibit()
	{
		$exhibit = new Exhibit;
		exhibit_builder_set_current_exhibit($exhibit);
		$this->assertSame($exhibit, $this->view->exhibit);
		$exhibit->title = 'test';
		// Ensures that the view is actually referencing the same object.
		$this->assertSame($exhibit, $this->view->exhibit);
	}
	
	/**
	 * Tests whether is_current_exhibit correctly determines which exhibit is current.
	 */
	public function testIsCurrentExhibit()
	{
		$exhibitOne = new Exhibit;
		$exhibitOne->id = 1;
		$exhibitTwo = new Exhibit;
		$exhibitTwo->id = 2;
		$this->assertFalse(exhibit_builder_is_current_exhibit($exhibitOne));
		$this->assertFalse(exhibit_builder_is_current_exhibit($exhibitTwo));
		
		$this->view->exhibit = $exhibitOne;
		$this->assertTrue(exhibit_builder_is_current_exhibit($exhibitOne));
		$this->assertFalse(exhibit_builder_is_current_exhibit($exhibitTwo));
		
		$this->view->exhibit = $exhibitTwo;
		$this->assertFalse(exhibit_builder_is_current_exhibit($exhibitOne));
		$this->assertTrue(exhibit_builder_is_current_exhibit($exhibitTwo));
	}
	
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
	
	/**
	 * Tests whether set_exhibits_for_loop correctly sets exhibits on the view.
	 */
	public function testSetExhibitsForLoop()
    {
		$exhibits = $this->_createExhibitArray();
		
		set_exhibits_for_loop($exhibits);
		$this->assertSame($exhibits, $this->view->exhibits);
		
		$exhibitsCount = 0;
		foreach ($this->view->exhibits as $exhibit) {
			$this->assertTrue(in_array($exhibit, $exhibits));
			$exhibitsCount++;
		}
    }
	
	/**
	 * Tests whether has_exhibits_for_loop correctly detects whether exhibits are set.
	 */
	public function testHasExhibitsForLoop()
	{
		$this->assertFalse(has_exhibits_for_loop());
		$this->view->exhibits = array();
		$this->assertFalse(has_exhibits_for_loop());
		$this->view->exhibits = $this->_createExhibitArray();
		$this->assertTrue(has_exhibits_for_loop());
	}
	
	/**
	 * Tests whether loop_exhibits loops over exhibits set on the view.
	 */
	public function testLoopExhibits()
	{
		$exhibits = $this->_createExhibitArray();
		$this->view->exhibits = $exhibits;
		
		$exhibitsCount = 0;
		while (loop_exhibits()) {
			$exhibit = $this->view->exhibit;
			$this->assertTrue(in_array($exhibit, $exhibits));
			$exhibitsCount++;
		}
		$this->assertEquals(10, $exhibitsCount);
	}
	
	/**
	 * Creates an array of 10 Exhibits.
	 */
	private function _createExhibitArray()
	{
		$exhibits = array();
		for ($i = 0; $i < 10; $i++) {
			$exhibit = new Exhibit;
			$exhibit->id = $i;
			$exhibits[] = $exhibit;
		}
		return $exhibits;
	}
}