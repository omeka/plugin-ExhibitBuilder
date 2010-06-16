<?php
/**
 * Tests for exhibit function
 */
class ExhibitTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit() returns the correct value.
     *
     * @uses exhibit()
     **/
    public function testCanRetrieveCorrectExhibitValue() 
    {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');

        // Exhibit Title
        $exhibitTitle = exhibit('Title');
        $this->assertEquals('Exhibit Title', $exhibitTitle);

        // Exhibit Description
        $exhibitDescription = exhibit('Description');
        $this->assertEquals('Exhibit Description', $exhibitDescription);

        // Exhibit Description
        $exhibitCredits = exhibit('Credits');
        $this->assertEquals('Jim Safley', $exhibitCredits);

        // Exhibit Slug
        $exhibitSlug = exhibit('slug');
        $this->assertEquals('exhibit-title', $exhibitSlug);    
    }
}