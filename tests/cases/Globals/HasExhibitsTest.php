<?php
/**
 * Tests for has_exhibits function
 */
class HasExhibitsTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether has_exhibits returns true when exhibits exist and false when they don't exist.
     *
     * @uses has_exhibits
     **/
    public function testHasExhibits() 
    {
        $this->assertFalse(has_exhibits(), 'Should not have exhibits!');
        $this->helper->createNewExhibits();
        $this->assertTrue(has_exhibits(), 'Should have exhibits!');
    }
}