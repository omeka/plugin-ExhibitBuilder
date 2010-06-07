<?php

class ExhibitFunctions_Test extends ExhibitBuilder_TestCase {
    
    protected function _createNewExhibit($isPublic, $isFeatured, $title, $description, $credits)
	{
		$exhibit = new Exhibit;
		$exhibit->public = $isPublic ? 1 : 0;
        $exhibit->featured = $isFeatured ? 1 : 0;
		$exhibit->title = $title;
    	$exhibit->description = $description;
    	$exhibit->credits = $credits;
    	$exhibit->save();
	}
	
	protected function _createNewExhibits($numberPublic = 5, $numberPrivate = 5, $numberPublicFeatured = 5) 
	{
        for ($i=0; $i < $numberPublic; $i++) {
            $this->_createNewExhibit(1, 0, 'Test Public Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i);
        }
        for ($i=0; $i < $numberPublicFeatured; $i++) {
            $this->_createNewExhibit(1, 1, 'Test Public Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i);   
        }
        for ($i=0; $i < $numberPrivate; $i++) {
            $this->_createNewExhibit(0, 0, 'Test Private Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i);
        }
	}
    
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
}