<?php
class ExhibitFunctions_Test extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit_builder_get_exhibits returns all available exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetExhibits() 
    {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits();
        $this->assertEquals(20, count($exhibits));
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all public exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPublicExhibits() 
    {        
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 1));
        $this->assertEquals(10, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertTrue((bool)$exhibit->public);
        }
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all private exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPrivateExhibits() 
    {        
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 0));
        $this->assertEquals(10, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertFalse((bool)$exhibit->public);
        }
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all public and featured exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPublicFeaturedExhibits() 
    {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 1, 'featured' => 1));
        $this->assertEquals(5, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertTrue((bool)$exhibit->public);
            $this->assertTrue((bool)$exhibit->featured);
        }
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all public and not featured exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPublicNotFeaturedExhibits() 
    {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 1, 'featured' => 0));
        $this->assertEquals(5, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertTrue((bool)$exhibit->public);
            $this->assertFalse((bool)$exhibit->featured);
        }
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all private and not featured exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPrivateNotFeaturedExhibits() 
    {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 0, 'featured' => 0));
        $this->assertEquals(5, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertFalse((bool)$exhibit->public);
            $this->assertFalse((bool)$exhibit->featured);
        }
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all private and featured exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetPrivateFeaturedExhibits() 
    {
        $this->_createNewExhibits();
        $exhibits = exhibit_builder_get_exhibits(array('public' => 0, 'featured' => 1));
        $this->assertEquals(5, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertFalse((bool)$exhibit->public);
            $this->assertTrue((bool)$exhibit->featured);
        }
    }

    /**
     * Tests whether total_exhibits returns correct count.
     *
     * @uses total_exhibits
     **/
    public function testCanGetExhibitCount() 
    {
        $publicNotFeaturedExhibitCount = 2;
        $publicFeaturedExhibitCount = 3;
        $privateNotFeaturedExhibitCount = 4;
        $privateFeaturedExhibitCount = 5;

        $expectedTotalCount = $publicNotFeaturedExhibitCount + $publicFeaturedExhibitCount + $privateNotFeaturedExhibitCount + $privateFeaturedExhibitCount;

        $this->_createNewExhibits($publicNotFeaturedExhibitCount, $publicFeaturedExhibitCount, $privateNotFeaturedExhibitCount, $privateFeaturedExhibitCount);
        $actualTotalCount = total_exhibits();
        $this->assertEquals($expectedTotalCount, $actualTotalCount); 
    }

    /**
     * Tests whether exhibit_builder_recent_exhibits() returns the correct number.
     *
     * @uses exhibit_builder_recent_exhibits()
     **/
    public function testCanGetRecentExhibits() 
    {
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
    public function testHasExhibits() 
    {
        $this->assertFalse(has_exhibits(), 'Should not have exhibits!');
        $this->_createNewExhibits();
        $this->assertTrue(has_exhibits(), 'No exhibits!');
    }

    /**
     * Tests whether has_exhibits_for_loop returns true.
     *
     * @uses has_exhibits_for_loop
     **/
    public function testHasExhibitsForLoop() 
    {
        $this->_createNewExhibits();
        $this->dispatch('exhibits');
        $this->assertTrue(has_exhibits_for_loop(), 'No exhibits for loop!');
    }

    /**
     * Tests whether has_exhibits_for_loop returns false.
     *
     * @uses has_exhibits_for_loop
     **/
    public function testHasNoExhibitsForLoop() 
    {
        $this->dispatch('exhibits');
        $this->assertFalse(has_exhibits_for_loop(), 'Should not have exhibits for loop!');
    }

    /**
     * Tests whether exhibit_builder_get_current_exhibit returns 
     * true, and contains correct info.
     *
     * @uses exhibit_builder_get_current_exhibit
     **/
    public function testGetCurrentExhibit() 
    {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $exhibit = exhibit_builder_get_current_exhibit();        
        $this->assertTrue(!empty($exhibit), 'No current exhibit!');
        $this->assertEquals('Exhibit Title', $exhibit->title);
        $this->assertEquals('Exhibit Description', $exhibit->description);
        $this->assertEquals('Jim Safley', $exhibit->credits);
        $this->assertEquals('exhibit-title', $exhibit->slug);
    }

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

    /**
     * Tests whether an exhibit can have '0' for a slug.  
     * Sometimes empty() is used when it shouldn't be used, so this double-checks this.
     *
     * @uses exhibit_builder_get_exhibits()
     **/
    public function testCanUseZeroForSlug()
    {
        $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley', '0');
        $exhibits = exhibit_builder_get_exhibits(array('public' => 1));
        $this->assertEquals(1, count($exhibits));
        $exhibit = $exhibits[0];
        $this->assertEquals('0', $exhibit->slug);
    }

    /**
     * Tests whether exhibit_builder_is_current_exhibit() returns the correct value.
     *
     * @uses exhibit_builder_is_current_exhibit() 
     **/
    public function testIsCurrentExhibit()
    {
        $exhibit = $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $exhibit2 = $this->_createNewExhibit(1, 0, 'Exhibit Title 2', 'Exhibit Description 2', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        $this->assertTrue(exhibit_builder_is_current_exhibit($exhibit));
        $this->assertFalse(exhibit_builder_is_current_exhibit($exhibit2));
    }

    /**
     * Tests whether set_current_exhibit() returns the correct value.
     *
     * @uses exhibit_builder_is_current_exhibit(), set_current_exhibit()
     **/
    public function testSetCurrentExhibit()
    {
        $exhibit = $this->_createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley');
        $exhibit2 = $this->_createNewExhibit(1, 0, 'Exhibit Title 2', 'Exhibit Description 2', 'Jim Safley');
        $this->dispatch('exhibits/show/exhibit-title');
        set_current_exhibit($exhibit2);
        $this->assertTrue(exhibit_builder_is_current_exhibit($exhibit2));
        $this->assertFalse(exhibit_builder_is_current_exhibit($exhibit));
    }

    /**
     * Tests whether loop_exhibits() loops through the correct exhibits.
     *
     * @uses loop_exhibits(), get_current_exhibit()
     **/
    public function testLoopExhibits()
    { 
        $exhibitIds = array();
        
        $exhibit1 = $this->_createNewExhibit(1, 0, 'Exhibit Title 1', 'Exhibit Description 1', 'Jim Safley');
        $exhibitIds[] = $exhibit1->id;
        
        $exhibit2 = $this->_createNewExhibit(1, 0, 'Exhibit Title 2', 'Exhibit Description 2', 'Jim Safley');
        $exhibitIds[] = $exhibit2->id;

        $exhibit3 = $this->_createNewExhibit(1, 0, 'Exhibit Title 3', 'Exhibit Description 3', 'Jim Safley');
        $exhibitIds[] = $exhibit3->id;
        
        $exhibit4 = $this->_createNewExhibit(1, 0, 'Exhibit Title 4', 'Exhibit Description 4', 'Jim Safley');
        $exhibitIds[] = $exhibit4->id;


        $this->dispatch('exhibits');
        $exhibitCount = 0;
        while(loop_exhibits()) {
            $exhibitCount++;
            $exhibit = get_current_exhibit();
            $this->assertTrue(in_array($exhibit->id, $exhibitIds));
        }
        $this->assertEquals(4, $exhibitCount);
    }
    
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
    
    public function testSetExhibitsForLoop()
    {
        $exhibits = $this->_createNewExhibits();
        $exhibitIds = array();
        foreach($exhibits as $exhibit) {
            $exhibitIds[] = $exhibit->id;
        }
        
        $this->dispatch('exhibits');
        $currentExhibits = get_exhibits_for_loop();
        $this->assertEquals(10, count($currentExhibits));
        foreach($currentExhibits as $exhibit) {
            $this->assertTrue(in_array($exhibit->id, $exhibitIds));
        }
        
        $exhibits2 = $this->_createNewExhibits();
        $exhibitIds2 = array();
        foreach($exhibits2 as $exhibit) {
            $exhibitIds2[] = $exhibit->id;
        }
        
        set_exhibits_for_loop($exhibits2);
        
        $currentExhibits = get_exhibits_for_loop();
        $this->assertEquals(count($exhibits2), count($currentExhibits));
        foreach($currentExhibits as $exhibit) {
            $this->assertTrue(in_array($exhibit->id, $exhibitIds2));
        }
    }
}