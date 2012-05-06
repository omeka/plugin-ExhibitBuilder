<?php
/**
 * ExhibitController class
 *
 * @version $Id$
 * @copyright Center for History and New Media, 2007-20009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

require_once 'Exhibit.php';

class ExhibitBuilder_ExhibitsController extends Omeka_Controller_Action
{
    protected $session;

    public function init()
    {
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $this->_helper->db->setDefaultModelName('Exhibit');
        } else {
            $this->_modelClass = 'Exhibit';
        }
        $this->_browseRecordsPerPage = 10;

        require_once 'Zend/Session.php';
        $this->session = new Zend_Session_Namespace('Exhibit');
    }

    public function browseAction()
    {
        $request = $this->getRequest();
        $sortParam = $request->getParam('sort');
        $sortOptionValue = get_option('exhibit_builder_sort_browse');

        if (!isset($sortParam)) {
            switch ($sortOptionValue) {
                case 'alpha':
                    $request->setParam('sort', 'alpha');
                    break;
                case 'recent':
                    $request->setParam('sort', 'recent');
                    break;
            }
        }

        parent::browseAction();
    }

    protected function _findByExhibitSlug($exhibitSlug = null)
    {
        if (!$exhibitSlug) {
            $exhibitSlug = $this->_getParam('slug');
        }
        $exhibit = $this->getTable('Exhibit')->findBySlug($exhibitSlug);
        return $exhibit;
    }

    public function tagsAction()
    {
        $params = array_merge($this->_getAllParams(), array('type'=>'Exhibit'));
        $tags = $this->getTable('Tag')->findBy($params);
        $this->view->assign(compact('tags'));
    }

    public function showitemAction()
    {
        $itemId = $this->_getParam('item_id');
        $item = $this->findById($itemId, 'Item');

        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            return $this->errorAction();
        }

        if ($item && $exhibit->hasItem($item) ) {

            //Plugin hooks
            fire_plugin_hook('show_exhibit_item',  $item, $exhibit);

            return $this->renderExhibit(compact('exhibit', 'item'), 'item');
        } else {
            $this->flash(__('This item is not used within this exhibit.'));
            $this->redirect->gotoUrl('403');
        }
    }

    /**
     *
     * @return void
     **/
    public function itemsAction()
    {
        $results = $this->_helper->searchItems();

        // Build the pagination.
        $pagination = array(
            'per_page'=>$results['per_page'],
            'page'=>$results['page'],
            'total_results'=> $results['total_results']);
        Zend_Registry::set('pagination', $pagination);

        $this->view->items = $results['items'];
    }

    public function itemContainerAction()
    {
        $itemId = (int)$this->_getParam('item_id');
        $orderOnForm = (int)$this->_getParam('order_on_form');
        $item = get_db()->getTable('Item')->find($itemId);
        $this->view->item = $item;
        $this->view->orderOnForm = $orderOnForm;
    }

    public function showAction()
    {
        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            $this->errorAction();
        }

        $params = $this->getRequest()->getParams();
        unset($params['action']);
        unset($params['controller']);
        unset($params['module']);
        //loop through the page slugs to make sure each one actually exists
        //then render the last one
        //pass all the pages into the view so the breadcrumb can be built there
        unset($params['slug']); // don't need the exhibit slug
        $parentPages = array();
        $pageTable = $this->getDb()->getTable('ExhibitPage');

        foreach($params as $level=>$slug) {
            if(!empty($slug)) {
                $page = $pageTable->findBySlug($slug);
                if($page) {
                    $parentPages[] = $page;
                } else {
                    $this->errorAction();
                }
            }
        }
        $exhibitPage = array_pop($parentPages);

        //make sure each page really does have the next child page
        for($i=0 ; $i < count($parentPages) - 2; $i++) {
            $currPage = $parentPages[$i];
            $nextPage = $parentPages[$i + 1];
            if($nextPage->parent_id != $currPage->id) {
                $this->errorAction();
            }
        }

        fire_plugin_hook('show_exhibit', $exhibit, $exhibitPage);

        $this->renderExhibit(compact('exhibit', 'parentPages', 'exhibitPage'));
    }

    public function summaryAction()
    {
        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            $this->errorAction();
        }

        fire_plugin_hook('show_exhibit', $exhibit);
        $this->renderExhibit(compact('exhibit'), 'summary');
    }

    /**
     * Figure out how to render the exhibit.
     * 1) the view needs access to the shared directories
     * 2) if the exhibit has an associated theme, render the pages for that specific exhibit theme,
     *      otherwise display the generic theme pages in the main public theme
     *
     * @return void
     **/
    protected function renderExhibit($vars, $toRender = 'show')
    {
        extract($vars);
        $this->view->assign($vars);

        /* If we don't pass a valid value to $toRender, thow an exception. */
        if (!in_array($toRender, array('show', 'summary', 'item'))) {
            throw new Exception( 'You gotta render some stuff because whatever!' );
        }
        return $this->render($toRender);

    }

    public function addAction()
    {
        $exhibit = new Exhibit;
        return $this->processExhibitForm($exhibit, 'Add');
    }

    public function editAction()
    {
        $exhibit = $this->findById();
        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        return $this->processExhibitForm($exhibit, 'Edit');
    }

    public function deleteAction()
    {
        $exhibit = $this->findById();
        if (!exhibit_builder_user_can_delete($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        return parent::deleteAction();
    }

    /**
     * This is where all the redirects and page rendering goes
     *
     * @return mixed
     **/
    protected function processExhibitForm($exhibit, $actionName)
    {
        try {
            $retVal = $exhibit->saveForm($_POST);
            if ($retVal) {
                if (array_key_exists('add_page',$_POST)) {
                    //forward to addPage & unset the POST vars
                    unset($_POST);
                    $this->redirect->goto('add-page', null, null, array('id'=>$exhibit->id) );
                    return;
                } else if (array_key_exists('save_exhibit', $_POST)) {
                    $this->redirect->goto('edit', null, null, array('id' => $exhibit->id));
                }
            }
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        } catch (Exception $e) {
            $this->flash($e->getMessage());
        }

        if ($themeName = $exhibit->theme) {
            $theme = Theme::getAvailable($themeName);
        } else {
            $theme = null;
        }

        $this->view->assign(compact('exhibit', 'actionName', 'theme'));

        //@duplication see ExhibitsController::processSectionForm()
        //If the form submission was invalid
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->render('exhibit-metadata-form');
        }
    }

    public function themeConfigAction()
    {
        $exhibit = $this->findById();
        $themeName = (string)$exhibit->theme;

        // Abort if no specific theme is selected.
        if ($themeName == '') {
            $this->flashError(__('You must specifically select a theme in order to configure it.'));
            $this->redirect->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
            return;
        }

        $theme = Theme::getAvailable($themeName);
        $previousOptions = $exhibit->getThemeOptions();

        $form = new Omeka_Form_ThemeConfiguration(array(
            'themeName' => $themeName,
            'themeOptions' => $previousOptions
        ));

        $themeConfigIni = $theme->path . DIRECTORY_SEPARATOR . 'config.ini';

        if (file_exists($themeConfigIni) && is_readable($themeConfigIni)) {

            try {
                $pluginsIni = new Zend_Config_Ini($themeConfigIni, 'plugins');
                $excludeFields = $pluginsIni->exclude_fields;
                $excludeFields = explode(',', $excludeFields);

            } catch(Exception $e) {
                $excludeFields = array();
            }

            foreach ($excludeFields as $excludeField) {
                trim($excludeField);
                $form->removeElement($excludeField);
            }
        }

        // process the form if posted
        if ($this->getRequest()->isPost()) {
            $configHelper = new Omeka_Controller_Action_Helper_ThemeConfiguration;

            if (($newOptions = $configHelper->processForm($form, $_POST, $previousOptions))) {
                $exhibit->setThemeOptions($newOptions);
                $exhibit->save();

                $this->flashSuccess(__('The theme settings were successfully saved!'));
                $this->redirect->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
            }
        }

        $this->view->assign(compact('exhibit', 'form', 'theme'));
    }


    /**
     * Add a page to an exhibit
     *
     * 1st URL param = 'id' for the exhibit that will contain the page
     *
     **/
    public function addPageAction()
    {
        $db = get_db();
        $request = $this->getRequest();
        $exhibitId = $request->getParam('id');
        //check if a parent page is coming in
        $parentId = $request->getParam('parent');
        $exhibitPage = new ExhibitPage;
        $exhibitPage->exhibit_id = $exhibitId;
        $exhibit = $db->getTable('Exhibit')->find($exhibitId);
        //Set the order for the new page
        if($parentId) {
            $exhibitPage->parent_id = $parentId;
            $parent = $db->getTable('ExhibitPage')->find($parentId);
            $childCount = $parent->countChildPages();
        } else {
            $childCount = $exhibit->countTopPages();
        }

        $exhibitPage->order = $childCount +1;

        $success = $this->processPageForm($exhibitPage, 'Add', $exhibit);
        if ($success) {
            $this->flashSuccess("Changes to the exhibit's page were successfully saved!");
            return $this->redirect->goto('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }

    public function editPageContentAction()
    {
        $db = get_db();
        $exhibitPage = $this->findById(null,'ExhibitPage');
        $exhibit = $db->getTable('Exhibit')->find($exhibitPage->exhibit_id);


        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $layoutIni = $this->layoutIni($exhibitPage->layout);

        $layoutName = $layoutIni->name;
        $layoutDescription = $layoutIni->description;

        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibit);

        if ($success and array_key_exists('section_form', $_POST)) {
            //Return to the section form
            return $this->redirect->goto('edit-section', null, null, array('id'=>$exhibitSection->id));
        } else if ($success and array_key_exists('page_metadata_form', $_POST)) {
           return $this->redirect->goto('edit-page-metadata', null, null, array('id'=>$exhibitPage->id));
        } else if (array_key_exists('page_form',$_POST)) {
            //Forward to the addPage action (id is the section id)
            return $this->redirect->goto('add-page', null, null, array('id'=>$exhibitPage->Section->id));
        }

        $this->view->layoutName = $layoutName;
        $this->view->layoutDescription = $layoutDescription;

        $this->render('page-content-form');
    }

    public function editPageMetadataAction()
    {
        $exhibitPage = $this->findById(null,'ExhibitPage');
        $exhibitSection = $exhibitPage->Section;
        $exhibit = $exhibitSection->Exhibit;

        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibit);

        if ($success) {
            return $this->redirect->goto('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }

    protected function processPageForm($exhibitPage, $actionName, $exhibit = null)
    {
        $this->view->assign(compact('exhibit', 'exhibitSection', 'exhibitPage', 'actionName'));
        if (!empty($_POST)) {
            try {
                $success = $exhibitPage->saveForm($_POST);
                return true;
            } catch (Exception $e) {
                $this->flashError($e->getMessage());
                return false;
            }
        }
    }

    /**
     * @internal There's a lot of duplication between this and deleteSectionAction().  Is that a problem?
     * Not anymore! Die sections! Die!
     **/
    public function deletePageAction()
    {
        $exhibitPage = $this->findById(null,'ExhibitPage');

        if (!exhibit_builder_user_can_delete($exhibitSection->Exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $exhibitPage->delete();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->render('page-list');
        } else {
            //@TODO: what should the new redirect be?
            $this->redirect->goto('edit-section', null, null, array('id' => $exhibitSection->id) );
        }
    }

    public function pageListAction()
    {
        $this->view->exhibitSection = $this->findById(null, 'ExhibitSection');
        $this->render('page-list');
    }

    protected function findOrNew()
    {
        try {
            $exhibit = $this->findById();
        } catch (Exception $e) {
            $exhibit = new Exhibit;
        }
        return $exhibit;
    }

    protected function layoutIni($layout)
    {
        $iniPath = EXHIBIT_LAYOUTS_DIR . DIRECTORY_SEPARATOR. "$layout" . DIRECTORY_SEPARATOR . "layout.ini";
        if (file_exists($iniPath) && is_readable($iniPath)) {
            $ini = new Zend_Config_Ini($iniPath, 'layout');
            return $ini;
        }
        return false;
    }


    public function updatePageOrderAction()
    {

        $pages = json_decode($_POST['data'], true);

        try {
            $this->updatePageChildrenOrders($pages, null);
            $response = array('ok'=>'updated');
        } catch(Exception $e) {
            $response = array('error'=>$e->getMessage());
        }
        $this->_helper->json($pages);
    }

    private function updatePageChildrenOrders($pages, $parent_id)
    {
        foreach($pages as $index=>$page) {
            $exPage = $this->findById($page['id'], 'ExhibitPage');
            $exPage->parent_id = $parent_id;
            $exPage->order = $index + 1;
            $exPage->save();
            if(!empty($page['children'])) {
                $this->updatePageChildrenOrders($page['children'], $exPage->id);
            }
        }
    }


    /////END AJAX-ONLY ACTIONS
}



class ExhibitsController_BadSlug_Exception extends Zend_Controller_Exception
{
}
