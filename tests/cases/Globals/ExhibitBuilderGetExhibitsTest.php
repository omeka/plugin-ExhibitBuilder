<?php
/**
 * Tests for exhibit_builder_get_exhibits function
 */
class ExhibitBuilderGetExhibitsTest extends ExhibitBuilder_TestCase 
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
}