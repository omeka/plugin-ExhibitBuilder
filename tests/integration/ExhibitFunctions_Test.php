<?php

class ExhibitFunctions_Test extends ExhibitBuilder_TestCase {
    
    /**
     * Tests whether exhibit_builder_get_exhibits returns all available exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetExhibits() {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits();
        $this->assertEquals(15, count($exhibits));
    }
    
    /**
     * Tests whether exhibit_builder_get_exhibits returns all public exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPublicExhibits() {        
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 1));
        $this->assertEquals(10, count($exhibits));
    }
    
    /**
     * Tests whether exhibit_builder_get_exhibits returns all private exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPrivateExhibits() {        
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 0));
        $this->assertEquals(5, count($exhibits));
    }
    
    /**
     * Tests whether exhibit_builder_get_exhibits returns all public and featured exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPublicFeaturedExhibits() {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 1, 'featured' => 1));
        $this->assertEquals(5, count($exhibits));
    }
    
    /**
     * Tests whether total_exhibits returns correct count.
     *
     * @uses total_exhibits
     **/
    public function testCanGetExhibitCount() {
        $this->_createNewExhibits();
        $count = total_exhibits();
        
        $this->assertEquals(15, $count); 
    }
    
    /**
     * Tests whether exhibit_builder_recent_exhibits() returns the correct number.
     *
     * @uses exhibit_builder_recent_exhibits()
     **/
    public function testCanGetRecentExhibits() {
        $this->_createNewExhibits();
        
        $recentExhibits = exhibit_builder_recent_exhibits();
        $this->assertEquals(10, count($recentExhibits));
        
        $recentExhibitsLimited = exhibit_builder_recent_exhibits(3);
        $this->assertEquals(3, count($recentExhibitsLimited));
    }
    
    /**
     * Tests whether has_exhibits returns true.
     *
     * @uses has_exhibits
     **/
    public function testHasExhibits() {
        $this->_createNewExhibits();
        $this->assertTrue(has_exhibits(), 'No exhibits!');
    }
    
    /**
     * Tests whether has_exhibits_for_loop returns true.
     *
     * @uses has_exhibits_for_loop
     **/
    public function testHasExhibitsForLoop() {
        $this->_createNewExhibits();
        $this->dispatch('exhibits');
        $this->assertTrue(has_exhibits_for_loop(), 'No exhibits for loop!');
    }
    
    /**
     * Tests whether exhibit_builder_get_current_exhibit returns 
     * true, and contains correct info.
     *
     * @uses exhibit_builder_get_current_exhibit
     **/
    public function testGetCurrentExhibit() {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit description.', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibit = exhibit_builder_get_current_exhibit();        
        $this->assertTrue(!empty($exhibit), 'No current exhibit!');
        $this->assertThat($exhibit->title, $this->stringContains('Exhibit Title'));
    }
    
    /**
     * Tests whether exhibit_builder_exhibit_uri returns correct URI.
     *
     * @uses exhibit_builder_exhibit_uri
     **/
    public function testGetCorrectExhibitUri() {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit description.', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibitUri = exhibit_builder_exhibit_uri();
        $this->assertThat($exhibitUri, $this->stringContains('exhibits/show/exhibit-title'));
    }
    
    /**
     * Tests whether exhibit() returns the correct value.
     *
     * @uses exhibit()
     **/
    public function testCanRetrieveCorrectExhibitValue() {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit description.', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        
        // Exhibit Title
        $exhibitTitle = exhibit('Title');
        $this->assertThat($exhibitTitle, $this->stringContains("Exhibit Title"));
    
        // Exhibit Description
        $exhibitDescription = exhibit('Description');
        $this->assertThat($exhibitDescription, $this->stringContains("Exhibit description."));
    
        // Exhibit Description
        $exhibitCredits = exhibit('Credits');
        $this->assertThat($exhibitCredits, $this->stringContains("Jim Safley"));
        
        // Exhibit Slug
        $exhibitSlug = exhibit('slug');
        $this->assertThat($exhibitSlug, $this->stringContains("exhibit-title"));    
    }
}