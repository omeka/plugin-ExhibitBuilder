<?php
/**
 * Tests for link_to_exhibit function
 */
class LinkToExhibitTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether link_to_exhibit() returns the correct link for an exhibit
     *
     * @uses link_to_exhibit()
     **/
    public function testLinkToExhibit()
    {
        $exhibit = $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibitLink = link_to_exhibit('Wow');
        $this->assertThat($exhibitLink, $this->stringContains('exhibits/show/exhibit-title" >Wow</a>'));
        
        $exhibitLink = link_to_exhibit('Wow', array('class'=>'zany', 'id' => 'wowlink'));
        $this->assertThat($exhibitLink, $this->stringContains('exhibits/show/exhibit-title" class="zany" id="wowlink">Wow</a>'));
        
        $exhibit2 = $this->_createNewExhibit(1, 0, 'Exhibit Title 2', 'Exhibit Description 2', 'Jim Safley');
        $exhibitLink = link_to_exhibit('Wow', array('class'=>'zany', 'id' => 'wowlink'), null, null, $exhibit2);
        $this->assertThat($exhibitLink, $this->stringContains('exhibits/show/exhibit-title-2" class="zany" id="wowlink">Wow</a>'));
    }
}