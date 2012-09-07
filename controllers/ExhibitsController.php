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

class ExhibitBuilder_ExhibitsController extends Omeka_Controller_AbstractActionController
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
        $exhibit = $this->_helper->db->getTable()->findBySlug($exhibitSlug);
        return $exhibit;
    }

    public function tagsAction()
    {
        $params = array_merge($this->_getAllParams(), array('type'=>'Exhibit'));
        $tags = $this->_helper->db->getTable('Tag')->findBy($params);
        $this->view->assign(compact('tags'));
    }

    public function showitemAction()
    {
        $itemId = $this->_getParam('item_id');
        $item = $this->_helper->db->findById($itemId, 'Item');

        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            throw new Omeka_Controller_Exception_404;
        }

        if ($item && $exhibit->hasItem($item) ) {

            //Plugin hooks
            fire_plugin_hook('show_exhibit_item',  array('item' => $item, 'exhibit' => $exhibit));

            return $this->renderExhibit(compact('exhibit', 'item'), 'item');
        } else {
            $this->_helper->flashMessenger(__('This item is not used within this exhibit.'), 'error');
            throw new Omeka_Controller_Exception_403;
        }
    }

    public function itemContainerAction()
    {
        $itemId = (int)$this->_getParam('item_id');
        $orderOnForm = (int)$this->_getParam('order_on_form');
        $item = $this->_helper->db->getTable('Item')->find($itemId);
        $this->view->item = $item;
        $this->view->orderOnForm = $orderOnForm;
    }

    public function showAction()
    {
        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            throw new Omeka_Controller_Exception_404;
        }

        $params = $this->getRequest()->getParams();
        
        // Redirect to the public theme if accessing the page via admin theme.
        if (is_admin_theme()) {
            $url = WEB_ROOT . "/exhibits/show/{$params[slug]}/{$params[page_slug_1]}";
            $this->_helper->redirector->goToUrl($url);
        }
        
        unset($params['action']);
        unset($params['controller']);
        unset($params['module']);
        //loop through the page slugs to make sure each one actually exists
        //then render the last one
        //pass all the pages into the view so the breadcrumb can be built there
        unset($params['slug']); // don't need the exhibit slug
        $parentPages = array();
        $pageTable = $this->_helper->db->getTable('ExhibitPage');
        
        foreach($params as $level=>$slug) {
            if(!empty($slug)) {
                $page = $pageTable->findBySlug($slug);
                if($page) {
                    $parentPages[] = $page;
                } else {
                    throw new Omeka_Controller_Exception_404;
                }
            }
        }
        $exhibitPage = array_pop($parentPages);

        //make sure each page really does have the next child page
        for($i=0 ; $i < count($parentPages) - 2; $i++) {
            $currPage = $parentPages[$i];
            $nextPage = $parentPages[$i + 1];
            if($nextPage->parent_id != $currPage->id) {
                throw new Omeka_Controller_Exception_404;
            }
        }

        fire_plugin_hook('show_exhibit', array('exhibit' => $exhibit, 'exhibitPage' => $exhibitPage));

        $this->renderExhibit(compact('exhibit', 'parentPages', 'exhibitPage'));
    }

    public function summaryAction()
    {
        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            throw new Omeka_Controller_Exception_404;
        }
        
        // Redirect to the public theme if accessing the exhibit via admin theme.
        if (is_admin_theme()) {
            $url = WEB_ROOT . "/exhibits/show/{$exhibit->slug}";
            $this->_helper->redirector->goToUrl($url);
        }

        fire_plugin_hook('show_exhibit', array('exhibit' => $exhibit));
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
        $exhibit = $this->_helper->db->findById();
        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        return $this->processExhibitForm($exhibit, 'Edit');
    }

    public function deleteAction()
    {
        $exhibit = $this->_helper->db->findById();
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
                    $this->_helper->redirector('add-page', null, null, array('id'=>$exhibit->id) );
                    return;
                } else if (array_key_exists('save_exhibit', $_POST)) {
                    $this->_helper->redirector('edit', null, null, array('id' => $exhibit->id));
                }
            }
        } catch (Omeka_Validator_Exception $e) {
            $this->_helper->flashMessenger($e);
        } catch (Exception $e) {
            $this->_helper->flashMessenger($e->getMessage());
        }

        if ($themeName = $exhibit->theme) {
            $theme = Theme::getTheme($themeName);
        } else {
            $theme = null;
        }

        $this->view->assign(compact('exhibit', 'actionName', 'theme'));

        //If the form submission was invalid
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->render('exhibit-metadata-form');
        }
    }

    public function themeConfigAction()
    {
        $exhibit = $this->_helper->db->findById();
        $themeName = (string)$exhibit->theme;

        // Abort if no specific theme is selected.
        if ($themeName == '') {
            $this->_helper->flashMessenger(__('You must specifically select a theme in order to configure it.'), 'error');
            $this->_helper->redirector->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
            return;
        }

        $theme = Theme::getTheme($themeName);
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

                $this->_helper->_flashMessenger(__('The theme settings were successfully saved!'), 'success');
                $this->_helper->redirector->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
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
        $db = $this->_helper->db->getDb();
        $request = $this->getRequest();
        $exhibitId = $request->getParam('id');
        //check if a parent page is coming in
        $previousPageId = $request->getParam('previous');
        $exhibitPage = new ExhibitPage;
        $exhibitPage->exhibit_id = $exhibitId;
        $exhibit = $exhibitPage->getExhibit();

        //Set the order for the new page

        if($previousPageId) {
            //set the order to be right after the previous one. Page's beforeSave method will bump up later page orders as needed
            $previousPage = $db->getTable('ExhibitPage')->find($previousPageId);
            $exhibitPage->parent_id = $previousPage->parent_id;
            $exhibitPage->order = $previousPage->order + 1;
        } else {
            $childCount = $exhibit->countTopPages();
            $exhibitPage->order = $childCount +1;
        }



        $success = $this->processPageForm($exhibitPage, 'Add', $exhibit);
        if ($success) {
            $this->_helper->flashMessenger("Changes to the exhibit's page were successfully saved!", 'success');
            return $this->_helper->redirector('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }

    public function editPageContentAction()
    {
        $db = $this->_helper->db->getDb();
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');
        $exhibit = $db->getTable('Exhibit')->find($exhibitPage->exhibit_id);


        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $layoutIni = $this->layoutIni($exhibitPage->layout);

        $layoutName = $layoutIni->name;
        $layoutDescription = $layoutIni->description;

        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibit);

        if ($success and array_key_exists('page_metadata_form', $_POST)) {
           return $this->_helper->redirector('edit-page-metadata', null, null, array('id'=>$exhibitPage->id));
        } else if (array_key_exists('page_form',$_POST)) {
            //Forward to the addPage action (id is the exhibit)
            return $this->_helper->redirector('add-page', null, null, array('exhibit'=>$exhibitPage->exhibit_id, 'previous' => $exhibitPage->id));
        }

        $this->view->layoutName = $layoutName;
        $this->view->layoutDescription = $layoutDescription;

        $this->render('page-content-form');
    }

    public function editPageMetadataAction()
    {
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');

        $exhibit = $exhibitPage->getExhibit();

        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibit);

        if ($success) {
            return $this->_helper->redirector('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }

    protected function processPageForm($exhibitPage, $actionName, $exhibit = null)
    {
        $this->view->assign(compact('exhibit', 'exhibitPage', 'actionName'));
        if (!empty($_POST)) {
            try {
                $success = $exhibitPage->saveForm($_POST);
                return true;
            } catch (Exception $e) {
                $this->_helper->flashMessenger($e->getMessage(), 'error');
                return false;
            }
        }
    }

    public function deletePageAction()
    {
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');
        $exhibit = $exhibitPage->getExhibit();
        if (!exhibit_builder_user_can_delete($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $exhibitPage->delete();
        $this->_helper->redirector->gotoUrl('exhibits/edit/' . $exhibit->id );
    }

    protected function findOrNew()
    {
        try {
            $exhibit = $this->_helper->db->findById();
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
            $exPage = $this->_helper->db->findById($page['id'], 'ExhibitPage');
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
