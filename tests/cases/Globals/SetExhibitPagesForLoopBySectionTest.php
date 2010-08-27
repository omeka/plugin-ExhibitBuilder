<?php

/**
 * Tests for set_exhibit_pages_for_loop_by_section function
 */
class SetExhibitPagesForLoopBySectionTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $this->exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($this->exhibit->exists());

        $this->exhibitSection = $this->helper->createNewExhibitSection($this->exhibit, 'Exhibit Section Title', 'Exhibit Section Description', 'exhibitsectionslug');
        $this->assertTrue($this->exhibitSection->exists());

        $this->maxExhibitPageCount = 6;
        $this->exhibitPageSlugs = array();
        for($i = 1; $i <= $this->maxExhibitPageCount; $i++) {
            $exhibitPage = $this->helper->createNewExhibitPage($this->exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, $i, 'text');
            $this->assertTrue($exhibitPage->exists());
            $this->exhibitPageSlugs[] = $exhibitPage->slug;
        }
    }

    /**
     * Tests whether set_exhibit_pages_for_loop_by_section correctly sets exhibit pages on the view when the exhibit section is specified.
     */
    public function testSetExhibitPagesForLoopBySectionWhenSectionIsSpecified()
    {   
        set_exhibit_pages_for_loop_by_section($this->exhibitSection);
        $exhibitPageCount = 0;
        foreach (__v()->exhibitPages as $exhibitPage) {
            $this->assertTrue(in_array($exhibitPage->slug, $this->exhibitPageSlugs));
            $exhibitPageCount++;
        }
        $this->assertEquals($this->maxExhibitPageCount, $exhibitPageCount);
    }
    
    /**
     * Tests whether set_exhibit_pages_for_loop_by_section correctly sets exhibit pages on the view when the exhibit section is not specified.
     */
    public function testSetExhibitPagesForLoopBySectionWhenSectionIsNotSpecified()
    {
        exhibit_builder_set_current_exhibit($this->exhibit);
        exhibit_builder_set_current_section($this->exhibitSection);
        
        // Make sure it uses the current exhibit by default
        set_exhibit_pages_for_loop_by_section();
        $exhibitPageCount = 0;
        foreach (__v()->exhibitPages as $exhibitPage) {
            $this->assertTrue(in_array($exhibitPage->slug, $this->exhibitPageSlugs));
            $exhibitPageCount++;
        }
        $this->assertEquals($this->maxExhibitPageCount, $exhibitPageCount);
    }
}