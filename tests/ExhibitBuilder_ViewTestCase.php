<?php

require_once EXHIBIT_BUILDER_DIR . '/models/Exhibit.php';
require_once EXHIBIT_BUILDER_DIR . '/models/ExhibitPage.php';


class ExhibitBuilder_ViewTestCase extends PHPUnit_Framework_TestCase
{
    protected $view;

    public function setUp()
    {
        $this->view = new Omeka_View;
        Zend_Registry::set('view', $this->view);

        $bootstrap = new Omeka_Test_Bootstrap;
        $bootstrap->getContainer()->db = $this->getMock('Omeka_Db', null, array(null));
        Zend_Registry::set('bootstrap', $bootstrap);
    }

    public function tearDown()
    {
        Zend_Registry::_unsetInstance();
    }

    /**
     * Creates an array of exhibits.
     * @param $maxExhibitCount The number of exhibits to create
     * @return array An array of exhibits
     **/
    protected function _createExhibitArray($maxExhibitCount=10)
    {
        $exhibits = array();
        for ($i = 0; $i < $maxExhibitCount; $i++) {
            $exhibit = new Exhibit;
            $exhibit->id = $i;
            $exhibits[] = $exhibit;
        }
        return $exhibits;
    }



    /**
     * Creates an array of exhibit pages.
     * @param $maxExhibitPageCount The number of exhibit pages to create
     * @return array An array of exhibit pages
     **/
    protected function _createExhibitPageArray($maxExhibitPageCount=10)
    {
        $exhibitPages = array();
        for ($i = 0; $i < $maxExhibitPageCount; $i++) {
            $exhibitPage = new ExhibitPage;
            $exhibitPage->id = $i;
            $exhibitPages[] = $exhibitPage;
        }
        return $exhibitPages;
    }
}
