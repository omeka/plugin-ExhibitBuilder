<?php

require_once EXHIBIT_BUILDER_DIR . '/helpers/ExhibitSectionFunctions.php';

/**
 * Tests for exhibit_builder_set_current_section function
 */
class ExhibitBuilderSetCurrentSectionTest extends ExhibitBuilder_ViewTestCase 
{
	/**
	 * Tests whether set_current_exhibit_section correctly sets an exhibit on the view.
	 */
	public function testExhibitBuilderSetCurrentSection()
	{
		$exhibitSection = new ExhibitSection;
		exhibit_builder_set_current_section($exhibitSection);
		$this->assertSame($exhibitSection, $this->view->exhibitSection);
		$exhibitSection->title = 'test';
		// Ensures that the view is actually referencing the same object.
		$this->assertSame($exhibitSection, $this->view->exhibitSection);
	}
}