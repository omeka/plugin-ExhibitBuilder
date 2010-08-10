<?php
/**
 * Tests for exhibit_builder_get_exhibits function
 */
class ExhibitBuilderGetExhibitsTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $this->helper->createNewExhibits();
        
        // This test was originally written assuming super user
        // TODO: replicate testing when not logged in
        $this->user = $this->db->getTable('User')->find(1);
        Omeka_Context::getInstance()->setCurrentUser($this->user);
    }

    /**
     * Tests whether exhibit_builder_get_exhibits returns all available exhibits.
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testCanGetExhibits() 
    {
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
        $exhibits = exhibit_builder_get_exhibits(array('public' => 0, 'featured' => 1));
        $this->assertEquals(5, count($exhibits));
        foreach($exhibits as $exhibit) {
            $this->assertFalse((bool)$exhibit->public);
            $this->assertTrue((bool)$exhibit->featured);
        }
    }
}