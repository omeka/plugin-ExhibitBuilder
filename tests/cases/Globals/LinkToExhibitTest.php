<?php
/**
 * Tests for link_to_exhibit function
 */
class LinkToExhibitTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether link_to_exhibit() returns the correct link for an exhibit
     *
     * @uses link_to_exhibit()
     */
    public function testLinkToExhibit()
    {
        $exhibit = $this->helper->createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibitLink = link_to_exhibit('Wow');
        $this->assertThat($exhibitLink, $this->stringContains('exhibits/show/exhibit-title" >Wow</a>'));

        $exhibitLink = link_to_exhibit('Wow', array('class'=>'zany', 'id' => 'wowlink'));
        $this->assertThat($exhibitLink, $this->stringContains('exhibits/show/exhibit-title" class="zany" id="wowlink">Wow</a>'));

        $exhibit2 = $this->helper->createNewExhibit(1, 0, 'Exhibit Title 2', 'Exhibit Description 2', 'Jim Safley');

        $this->assertEquals('exhibit-title-2', $exhibit2->slug);
        $exhibitLink = link_to_exhibit('Wow', array('class'=>'zany', 'id' => 'wowlink'), null, $exhibit2);
        $this->assertThat($exhibitLink, $this->stringContains('exhibits/show/exhibit-title-2" class="zany" id="wowlink">Wow</a>'));
    }
}
