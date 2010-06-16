<?php
/**
 * Tests for has_exhibits function
 */
class HasExhibitsTest extends ExhibitBuilder_TestCase 
{
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
}