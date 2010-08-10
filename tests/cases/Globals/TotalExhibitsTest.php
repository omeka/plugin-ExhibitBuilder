<?php
/**
 * Tests for total_exhibits function
 */
class TotalExhibitsTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $publicNotFeaturedExhibitCount = 2;
        $publicFeaturedExhibitCount = 3;
        $privateNotFeaturedExhibitCount = 4;
        $privateFeaturedExhibitCount = 5;

        $this->expectedTotalCount = $publicNotFeaturedExhibitCount + $publicFeaturedExhibitCount + $privateNotFeaturedExhibitCount + $privateFeaturedExhibitCount;
        $this->expectedPublicCount = $publicNotFeaturedExhibitCount + $publicFeaturedExhibitCount;

        $this->helper->createNewExhibits($publicNotFeaturedExhibitCount, $publicFeaturedExhibitCount, $privateNotFeaturedExhibitCount, $privateFeaturedExhibitCount);
    }

    /**
     * Tests whether total_exhibits returns correct count.
     *
     * @uses total_exhibits
     **/
    public function testCanGetExhibitCountForAdmin()
    {
        $this->user = $this->db->getTable('User')->find(1);
        Omeka_Context::getInstance()->setCurrentUser($this->user);

        $actualTotalCount = total_exhibits();
        $this->assertEquals($this->expectedTotalCount, $actualTotalCount);
    }

        /**
     * Tests whether total_exhibits returns correct count.
     *
     * @uses total_exhibits
     **/
    public function testCanGetExhibitCountForPublic()
    {
        $actualTotalCount = total_exhibits();
        $this->assertEquals($this->expectedPublicCount, $actualTotalCount);
    }
}