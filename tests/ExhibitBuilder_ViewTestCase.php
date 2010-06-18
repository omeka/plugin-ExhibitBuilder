<?php

require_once HELPERS;
require_once EXHIBIT_BUILDER_DIR . '/models/Exhibit.php';
require_once EXHIBIT_BUILDER_DIR . '/models/ExhibitSection.php';


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
	 * @param $maxExhibitCount The number of exhibits to create
	 * @return array An array of exhibits
	 **/
	protected function _createExhibitArray($maxExhibitCount=10)
	{
		$exhibits = array();
		for ($i = 0; $i < $maxExhibitCount; $i++) {
			$exhibit = new Exhibit;
			$exhibit->id = $i;
			$exhibits[] = $exhibit;
		}
		return $exhibits;
	}
	
	/**
	 * Creates an array of exhibit sections.
	 * @param $maxExhibitSectionCount The number of exhibit sections to create
	 * @return array An array of exhibit sections
	 **/
	protected function _createExhibitSectionArray($maxExhibitSectionCount=10)
	{
		$exhibits = array();
		for ($i = 0; $i < $maxExhibitSectionCount; $i++) {
			$exhibitSection = new ExhibitSection;
			$exhibitSection->id = $i;
			$exhibitSections[] = $exhibitSection;
		}
		return $exhibitSections;
	}
	
	public function tearDown()
	{
        Zend_Registry::_unsetInstance();
        Omeka_Context::resetInstance();
        Omeka_Controller_Flash::reset();
        parent::tearDown();
	}
}