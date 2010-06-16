<?php
/**
 * Tests for exhibit_builder_recent_exhibits function
 */
class ExhibitBuilderRecentExhibitsTest extends ExhibitBuilder_TestCase 
{
    /**
     * Tests whether exhibit_builder_recent_exhibits() returns the correct number.
     *
     * @uses exhibit_builder_recent_exhibits()
     **/
    public function testCanGetRecentExhibits() 
    {
        $maxExhibitCount = 10;
        $this->_createNewExhibits($maxExhibitCount);
        
        $recentExhibits = exhibit_builder_recent_exhibits();
        $this->assertEquals($maxExhibitCount, count($recentExhibits));

        $recentExhibitCount = 3;
        $recentExhibitsLimited = exhibit_builder_recent_exhibits($recentExhibitCount);
        $this->assertEquals($recentExhibitCount, count($recentExhibitsLimited));
    }
}