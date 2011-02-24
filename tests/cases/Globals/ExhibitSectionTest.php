<?php
/**
 * Tests for exhibit_section function
 */
class ExhibitSectionTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }
    /**
     * Tests whether exhibit_section() returns the correct value.
     *
     * @uses exhibit_section()
     **/
    public function testCanRetrieveCorrectExhibitSectionValue() 
    {
        $exhibit = $this->helper->createNewExhibit(true, false, 'Exhibit Title', 'Exhibit Description', 'Exhibit Credits', 'exhibitslug');
        $this->assertTrue($exhibit->exists());
        
        $maxExhibitSectionCount = 8;
        for($i = 1; $i <= $maxExhibitSectionCount; $i++) {
            $exhibitSection = $this->helper->createNewExhibitSection($exhibit, 'Exhibit Section Title ' . $i, 'Exhibit Section Description ' . $i, 'exhibitsectionslug' . $i, $i);
            $this->assertTrue($exhibitSection->exists());
            
            $exhibitPage = $this->helper->createNewExhibitPage($exhibitSection, 'Exhibit Page Title ' . $i, 'exhibitpageslug' . $i, 1);
            $this->assertTrue($exhibitPage->exists());
        }        
        $this->dispatch('exhibits/show/exhibitslug/exhibitsectionslug3');

        $exhibitSection = get_current_exhibit_section();
        $this->assertTrue($exhibitSection->exists());
        $this->assertEquals('Exhibit Section Title 3', $exhibitSection->title);
        $this->assertEquals('exhibitsectionslug3', $exhibitSection->slug);

        // Exhibit Section Title
        $this->assertEquals('Exhibit Section Title 3', exhibit_section('Title'));

        // Exhibit Section Description
        $this->assertEquals('Exhibit Section Description 3', exhibit_section('Description'));

        // Exhibit Section Order
        $this->assertEquals(3, exhibit_section('Order'));

        // Exhibit Section Slug
        $this->assertEquals('exhibitsectionslug3', exhibit_section('Slug'));
        
        // Exhibit Section Exhibit Id
        $this->assertEquals($exhibit->id, exhibit_section('Exhibit Id'));
    }
}
