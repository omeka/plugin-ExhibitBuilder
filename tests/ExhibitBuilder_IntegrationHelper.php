<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Testing helper for Exhibit Builder.
 */
class ExhibitBuilder_IntegrationHelper
{
    public function setUpPlugin()
    {
        $pluginHelper = new Omeka_Test_Helper_Plugin;
        $pluginHelper->setUp('ExhibitBuilder');
    }

    public function createNewExhibit($isPublic, $isFeatured, $title, $description, $credits, $slug='')
    {
        $exhibit = new Exhibit;
        $exhibit->public = $isPublic ? 1 : 0;
        $exhibit->featured = $isFeatured ? 1 : 0;
        $exhibit->title = $title;
        $exhibit->description = $description;
        $exhibit->credits = $credits;

        if ($slug != '') {
            $exhibit->slug = $slug;
        }

        $exhibit->save();

        return $exhibit;
    }

    public function createNewExhibits($numberPublicNotFeatured = 5, $numberPublicFeatured = 5, $numberPrivateNotFeatured = 5, $numberPrivateFeatured = 5)
    {
        $exhibits = array();
        for ($i=0; $i < $numberPublicNotFeatured; $i++) {
            $exhibits[] = $this->createNewExhibit(1, 0, 'Test Public Not Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'punf' . $i);
        }
        for ($i=0; $i < $numberPublicFeatured; $i++) {
            $exhibits[] = $this->createNewExhibit(1, 1, 'Test Public Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'puf' . $i);
        }
        for ($i=0; $i < $numberPrivateNotFeatured; $i++) {
            $exhibits[] = $this->createNewExhibit(0, 0, 'Test Private Not Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'prnf' . $i);
        }
        for ($i=0; $i < $numberPrivateFeatured; $i++) {
            $exhibits[] = $this->createNewExhibit(0, 1, 'Test Private Featured Exhibit '.$i, 'Description for '.$i, 'Credits for '.$i, 'prf' . $i);
        }

        return $exhibits;
    }


    public function createNewExhibitPage($exhibit, $parentPage = null, $title, $slug = '', $order = 1, $layout = 'text')
    {
        $exhibitPage = new ExhibitPage;
        $exhibitPage->exhibit_id = $exhibit->id;
        if($parentPage) {
            $exhibitPage->parent_id = $parentPage->id;
        }
        $exhibitPage->title = $title;
        $exhibitPage->layout = $layout;
        $exhibitPage->order = $order;

        if ($slug != '') {
            $exhibitPage->slug = $slug;
        }

        $exhibitPage->save();

        return $exhibitPage;
    }

    public function createNewItem($isPublic = true, $title = 'Item Title', $titleIsHtml = true)
    {
        $item = insert_item(array('public' => $isPublic),
                array(
                    'Dublin Core' => array(
                        'Title' => array(
                            array('text' => $title, 'html' => $titleIsHtml)
                        )
                    )
                )
            );
        return $item;
    }
}
