<?php
/**
 * Tests whether an exhibit can have zero for a slug
 */
class ExhibitCanHaveZeroForSlugTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    /**
     * Tests whether an exhibit can have '0' for a slug.  
     * Sometimes empty() is used when it shouldn't be used, so this double-checks this.
     **/
    public function testExhibitCanHaveZeroForSlug()
    {
        $this->helper->createNewExhibit(1, 0, 'Exhibit Title', 'Exhibit Description', 'Jim Safley', '0');
        $exhibits = get_records('Exhibit', array('public' => 1));
        $this->assertEquals(1, count($exhibits));
        $exhibit = $exhibits[0];
        $this->assertEquals('0', $exhibit->slug);
    }
}
