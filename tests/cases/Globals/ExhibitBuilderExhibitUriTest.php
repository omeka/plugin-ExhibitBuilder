<?php
/**
 * Tests for exhibit_builder_exhibit_uri function
 */
class ExhibitBuilderExhibitUriTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }
    /**
     * Tests whether exhibit_builder_exhibit_uri returns correct URI.
     *
     * @uses exhibit_builder_exhibit_uri
     */
    public function testGetCorrectExhibitUri() 
    {
        $this->helper->createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit description.', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibitUri = exhibit_builder_exhibit_uri();
        $this->assertThat($exhibitUri, $this->stringContains('exhibits/show/exhibit-title'));
    }
}
