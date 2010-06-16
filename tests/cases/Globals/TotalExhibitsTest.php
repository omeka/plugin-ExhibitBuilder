<?php
/**
 * Tests for total_exhibits function
 */
class TotalExhibitsTest extends ExhibitBuilder_TestCase 
{
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
}