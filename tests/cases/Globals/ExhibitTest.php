<?php
/**
 * Tests for exhibit function
 */
class ExhibitTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }
    
    /**
     * Tests whether exhibit() returns the correct value.
     *
     * @uses exhibit()
     **/
    public function testCanRetrieveCorrectExhibitValue() 
    {
        $this->helper->createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');

        // Exhibit Title
        $this->assertEquals('Exhibit Title', exhibit('Title'));

        // Exhibit Description
        $this->assertEquals('Exhibit Description', exhibit('Description'));

        // Exhibit Credits
        $this->assertEquals('Jim Safley', exhibit('Credits'));

        // Exhibit Slug
        $this->assertEquals('exhibit-title', exhibit('slug'));    
    }
}
