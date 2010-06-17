<?php

require_once HELPERS;
require_once EXHIBIT_BUILDER_DIR . '/models/Exhibit.php';

class ExhibitBuilder_ViewTestCase extends PHPUnit_Framework_TestCase 
{
	protected $view;
	
	public function setUp()
	{
		$this->view = new Omeka_View;
		Zend_Registry::set('view', $this->view);
	}
	
	/**
	 * Creates an array of exhibits.
	 * @param $maxExhibits The number of exhibits to create
	 * @return array An array of exhibits
	 **/
	protected function _createExhibitArray($maxExhibits=10)
	{
		$exhibits = array();
		for ($i = 0; $i < $maxExhibits; $i++) {
			$exhibit = new Exhibit;
			$exhibit->id = $i;
			$exhibits[] = $exhibit;
		}
		return $exhibits;
	}
	
	public function tearDown()
	{
        Zend_Registry::_unsetInstance();
        Omeka_Context::resetInstance();
        Omeka_Controller_Flash::reset();
        parent::tearDown();
	}
}