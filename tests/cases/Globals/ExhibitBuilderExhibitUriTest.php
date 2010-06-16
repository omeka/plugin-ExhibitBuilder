<?php
/**
 * Tests for exhibit_builder_exhibit_uri function
 */
class ExhibitBuilderExhibitUriTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit_builder_exhibit_uri returns correct URI.
     *
     * @uses exhibit_builder_exhibit_uri
     **/
    public function testGetCorrectExhibitUri() 
    {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit description.', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibitUri = exhibit_builder_exhibit_uri();
        $this->assertThat($exhibitUri, $this->stringContains('exhibits/show/exhibit-title'));
    }
}