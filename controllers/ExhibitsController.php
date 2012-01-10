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
        
        $sectionSlug = $this->_getParam('section_slug');
        $exhibitSection = $exhibit->getSectionBySlug($sectionSlug);
  
        if ($item && $exhibit->hasItem($item) ) {
     
            //Plugin hooks
            fire_plugin_hook('show_exhibit_item',  $item, $exhibit);
     
            return $this->renderExhibit(compact('exhibit', 'exhibitSection', 'item'), 'item');
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
        
        $sectionSlug = $this->_getParam('section_slug');
        $exhibitSection = $exhibit->getSectionBySlug($sectionSlug);
        
        if ($exhibitSection) {
            $pageSlug = $this->_getParam('page_slug');
            $exhibitPage = $exhibitSection->getPageBySlug($pageSlug);            
            if (!$exhibitPage) {
                if ($pageSlug == '') {
                    $exhibitPage = $exhibitSection->getPageByOrder(1);
                }
            }
            if (!$exhibitPage) {
                $this->errorAction();
            }
        } else {
            $this->errorAction();
        }
        
        fire_plugin_hook('show_exhibit', $exhibit, $exhibitSection, $exhibitPage);
                
        $this->renderExhibit(compact('exhibit', 'exhibitSection', 'exhibitPage'));
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
                if (array_key_exists('add_section',$_POST)) {
                    //forward to addSection & unset the POST vars 
                    unset($_POST);
                    $this->redirect->goto('add-section', null, null, array('id'=>$exhibit->id) );
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
     * 1st URL param = 'id' for Exhibit
     *
     **/
    public function addSectionAction()
    {
        $exhibit = $this->findById();
        $exhibitSection = new ExhibitSection;
        $exhibitSection->exhibit_id = $exhibit->id;
        
        //Give the new section a section order (1, 2, 3, ...)
        $numSections = $exhibit->getSectionCount();
        $exhibitSection->order = $numSections + 1;
        
        //Tell the plugin hook that we are adding a section
        $this->addSection = true;
        
        return $this->processSectionForm($exhibitSection, 'Add', $exhibit);
    }
    
    protected function processSectionForm($exhibitSection, $actionName, $exhibit = null)
    {       
        $retVal = false;
        
        try {
            //Section form may be prefixed with Section (like name="Section[title]") or it may not be, depending
            
            if (array_key_exists('Section', $_POST)) {
                $toPost = $_POST['Section'];
            } else {
                $toPost = $_POST;
            }
            $retVal = $exhibitSection->saveForm($toPost);
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        } catch (Exception $e) {
            $this->flashError($e->getMessage());
        }
                    
        //If successful form submission
        if ($retVal) {   
            
            //Forward around based on what submit button was pressed
            if (array_key_exists('page_form',$_POST)) {    
                //Forward to the addPage action (id is the section id)
                $this->redirect->goto('add-page', null, null, array('id'=>$exhibitSection->id));
                return;
            } else {
                // Only flash this success message if it is not going to the Add Page
                $this->flashSuccess("Changes to the exhibit's section were successfully saved!");
            }
            
            if (array_key_exists('section_form', $_POST)) {
                $this->redirect->goto('edit-section', null, null, array('id'=>$exhibitSection->id));
            }
        }

        $this->view->assign(compact('exhibit', 'exhibitSection', 'actionName'));
                
        // Render the big section form script if this is not an AJAX request.
        if (!$this->getRequest()->isXmlHttpRequest() ) {
            $this->render('section-metadata-form'); 
        } else {
            // This is for AJAX requests.
            
            // If the form submission was not valid, render the mini-form.
            if (!$retVal) {
                $this->render('sectionform');
            } else {
                // Otherwise render the partial that displays the list of sections.
                $this->render('section-list');
            }
            
        }
    }

    /**
     * Add a page to a section
     *
     * 1st URL param = 'id' for the section that will contain the page
     * 
     **/
    public function addPageAction()
    {
        $exhibitSection = $this->findById(null,'ExhibitSection');
        $exhibit = $exhibitSection->Exhibit;
                
        $exhibitPage = new ExhibitPage;
        $exhibitPage->section_id = $exhibitSection->id;       
                
        //Set the order for the new page
        $numPages = $exhibitSection->getPageCount();
        $exhibitPage->order = $numPages + 1;       
        
        $success = $this->processPageForm($exhibitPage, 'Add', $exhibitSection, $exhibit);
        if ($success) {
            $this->flashSuccess("Changes to the exhibit's page were successfully saved!");
            return $this->redirect->goto('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }
    
    public function editPageContentAction()
    {
        $exhibitPage = $this->findById(null,'ExhibitPage');
        $exhibitSection = $exhibitPage->Section;
        $exhibit = $exhibitSection->Exhibit;

        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $layoutIni = $this->layoutIni($exhibitPage->layout);
        
        $layoutName = $layoutIni->name;
        $layoutDescription = $layoutIni->description;

        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibitSection, $exhibit);        
        
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
        
        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibitSection, $exhibit);
        
        if ($success) {
            return $this->redirect->goto('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }
        
        $this->render('page-metadata-form');
    }

    protected function processPageForm($exhibitPage, $actionName, $exhibitSection = null, $exhibit = null) 
    {        
        $this->view->assign(compact('exhibit', 'exhibitSection', 'exhibitPage', 'actionName'));        
        if (!empty($_POST)) {
            try {
                $success = $exhibitPage->saveForm($_POST);
            } catch (Exception $e) {
                $this->flashError($e->getMessage());
            }
            return $success;
        }
    }
    
    /**
     * 1st URL param = Section ID
     *
     **/
    public function editSectionAction()
    {
        $exhibitSection = $this->findById(null, 'ExhibitSection');
        $exhibit = $exhibitSection->Exhibit;

        if (!exhibit_builder_user_can_edit($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }
        
        return $this->processSectionForm($exhibitSection, 'Edit', $exhibit);
    }
    
    public function deleteSectionAction()
    {
        //Delete the section and re-order the rest of the sections in the exhibit
        
        $exhibitSection = $this->findById(null,'ExhibitSection');
        $exhibit = $exhibitSection->Exhibit;

        if (!exhibit_builder_user_can_delete($exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $exhibitSection->delete();
        
        // If we are making an AJAX request to delete a section, return the XHTML for the list partial
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->view->exhibit = $exhibit;
            $this->render('section-list');
        } else {
            // For non-AJAX requests, redirect to the exhibits/edit page.
            $this->redirect->goto('edit', null, null, array('id'=>$exhibit->id) );
        }
    }
    
    /**
     * @internal There's a lot of duplication between this and deleteSectionAction().  Is that a problem?
     **/
    public function deletePageAction()
    {
        $exhibitPage = $this->findById(null,'ExhibitPage');
        $exhibitSection = $exhibitPage->Section;

        if (!exhibit_builder_user_can_delete($exhibitSection->Exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }
        
        $exhibitPage->delete();
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->view->exhibitSection = $exhibitSection;
            $this->render('page-list');
        } else {
            $this->redirect->goto('edit-section', null, null, array('id' => $exhibitSection->id) );
        }
    }
    
    /////HERE WE HAVE SOME AJAX-ONLY ACTIONS /////

    public function sectionListAction()
    {
        $this->view->exhibit = $this->findOrNew();
        return $this->render('section-list');
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
    
    /////END AJAX-ONLY ACTIONS
}



class ExhibitsController_BadSlug_Exception extends Zend_Controller_Exception 
{    
}
