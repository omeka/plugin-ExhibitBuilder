<?php
class ExhibitBuilder_404Test extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = false;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();

        $this->helper->createNewExhibits(1,0,0,0);
    }

    /**
     * Tests to make sure the exhibits controller will return a 404 error for a bad exhibit slug
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testError404WithBadExhibitSlug()
    {
        $exhibits = exhibit_builder_get_exhibits();
        $this->assertEquals(1, count($exhibits));
        $exhibit = $exhibits[0];
        $exhibit->slug = 'goodexhibitslug';
        $exhibit->save();
        $this->assertEquals('goodexhibitslug', $exhibit->slug, 'Bad exhibit slug.');

        $badExhibit = $this->db->getTable('Exhibit')->findBySlug('badexhibitslug');
        $this->assertEquals(null, $badExhibit);

        $this->setExpectedException('Omeka_Controller_Exception_404');
        $this->dispatch('exhibits/show/badexhibitslug');
    }

    /**
     * Tests to make sure the exhibits controller will not return a 404 error for a good exhibit slug
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testNoError404WithGoodExhibitSlug()
    {
        $exhibits = exhibit_builder_get_exhibits();
        $this->assertEquals(1, count($exhibits));
        $exhibit = $exhibits[0];
        $exhibit->slug = 'goodexhibitslug';
        $exhibit->save();
        $this->assertEquals('goodexhibitslug', $exhibit->slug, 'Bad exhibit slug.');

        try {
            $this->dispatch('exhibits/show/goodexhibitslug');
        } catch (Exception $e) {
            $this->fail('Should not have thrown a 404 error for a good exhibit slug.');
        }
    }

    /**
     * Tests to make sure the exhibits controller will return a 404 error for a bad exhibit page slug
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testError404WithBadExhibitPageSlug()
    {
        $exhibits = exhibit_builder_get_exhibits();
        $this->assertEquals(1, count($exhibits));
        $exhibit = $exhibits[0];
        $exhibit->slug = 'goodexhibitslug';
        $exhibit->save();
        $this->assertEquals('goodexhibitslug', $exhibit->slug, 'Bad exhibit slug.');

        $exhibitPage = new ExhibitPage;
        $exhibitPage->title = 'Test Page';
        $exhibitPage->order = 1;
        $exhibitPage->layout = 'image-list-left-thumbs';
        $exhibitPage->slug = 'goodexhibitpageslug';
        $exhibitPage->exhibit_id = $exhibit->id;
        $exhibitPage->save();
        $this->assertTrue($exhibitPage->exists());

        $badExhibitPage = $this->db->getTable('ExhibitPage')->findBySlug('badexhibitpageslug');
        $this->assertEquals(null, $badExhibitPage);

        $this->setExpectedException('Omeka_Controller_Exception_404');
        $this->dispatch('exhibits/show/goodexhibitslug/goodexhibitslug/badexhibitpageslug');
    }

    /**
     * Tests to make sure the exhibits controller will return a 404 error for a bad exhibit page slug
     *
     * @uses exhibit_builder_get_exhibits
     **/
    public function testNoError404WithGoodExhibitPageSlug()
    {
        $exhibits = exhibit_builder_get_exhibits();
        $this->assertEquals(1, count($exhibits));
        $exhibit = $exhibits[0];
        $exhibit->slug = 'goodexhibitslug';
        $exhibit->save();
        $this->assertEquals('goodexhibitslug', $exhibit->slug, 'Bad exhibit slug.');

        $exhibitPage = new ExhibitPage;
        $exhibitPage->title = 'Test Page';
        $exhibitPage->order = 1;
        $exhibitPage->layout = 'image-list-left-thumbs';
        $exhibitPage->slug = 'goodexhibitpageslug';
        $exhibitPage->exhibit_id = $exhibit->id;
        $exhibitPage->save();
        $this->assertTrue($exhibitPage->exists());

        try {
            $this->dispatch('exhibits/show/goodexhibitslug/goodexhibitpageslug');
        } catch (Exception $e) {
            $this->fail('Should not have thrown a 404 error for a good exhibit page slug.');
        }
    }
}
