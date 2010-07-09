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
        $this->_modelClass = 'Exhibit';
        $this->_browseRecordsPerPage = 10;
        
        require_once 'Zend/Session.php';
        $this->session = new Zend_Session_Namespace('Exhibit');
    }
    
    protected function _findByExhibitSlug($exhibitSlug = null) 
    {        
        if (!$exhibitSlug) {
            $exhibitSlug = $this->_getParam('slug');
        }
        $exhibit = $this->_table->findBySlug($exhibitSlug);        
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
            $this->errorAction();    
        }
        
        $sectionSlug = $this->_getParam('section_slug');
        $exhibitSection = $exhibit->getSectionBySlug($sectionSlug);
  
        if ($item and $this->_table->exhibitHasItem($exhibit->id, $item->id) ) {
     
            //Plugin hooks
            fire_plugin_hook('show_exhibit_item',  $item, $exhibit);
     
            return $this->renderExhibit(compact('exhibit', 'exhibitSection', 'item'), 'item');
        } else {
            $this->flash('This item is not used within this exhibit.');
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
        /*  If there is a theme, render the header/footer and layout page,
            Otherwise render the default exhibits/show.php page
        */
        extract($vars);
        $this->view->assign($vars);
        if (!empty($exhibit->theme)) {
            /**
             * Define some paths we need. 
             * $exhibitThemePhysicalPath - The physical path to the exhibit's theme
             * $exhibitThemeWebPath - The web path to the exhibit's theme
             */
            $exhibitThemePhysicalPath = PUBLIC_THEME_DIR.DIRECTORY_SEPARATOR.$exhibit->theme;
            $exhibitThemeWebPath = WEB_PUBLIC_THEME.DIRECTORY_SEPARATOR.$exhibit->theme;
            
            /* 
             * This tells the view where the our exhibit theme's scripts and 
             * assets are. Otherwise the view will use scripts and assets from 
             * the public theme.
             */
            $this->view->addScriptPath($exhibitThemePhysicalPath);
            $this->view->addAssetPath($exhibitThemePhysicalPath, $exhibitThemeWebPath);
        }

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
        if ($user = $this->getCurrentUser()) {
            $exhibit = $this->findById();
            if ($this->isAllowed('editAll', 'ExhibitBuilder_Exhibits') || ($this->isAllowed('editSelf', 'ExhibitBuilder_Exhibits') && $exhibit->wasAddedBy($user))) {
                return $this->processExhibitForm($exhibit, 'Edit');
            }
        }
        throw new Omeka_Controller_Exception_403();
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
            $this->flashError("You must specifically select a theme in order to configure it.");
            $this->redirect->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
            return;
        }
        $form = new Omeka_Form_ThemeConfiguration(array('themeName' => $themeName));
        $theme = Theme::getAvailable($themeName);
        $previousOptions = $exhibit->getThemeOptions();
        $hiddenFieldPrefix = Omeka_Form_ThemeConfiguration::THEME_FILE_HIDDEN_FIELD_NAME_PREFIX;
        
        if (empty($previousOptions)) {
            // Unset hidden field values if there are no pre-existing options.
            $elements = $form->getElements();
            foreach ($elements as $element) {
                if (strpos($element->getName(), $hiddenFieldPrefix) == 0) { 
                    $element->setValue(null);
                }
            }
        } else {
            // Replace form values and hidden values with exhibit theme options.
            foreach($previousOptions as $key => $value) {
                if ($form->getElement($key)) {
                    $form->$key->setValue($value);
                }
                $hiddenKey = $hiddenFieldPrefix.$key;
                if ($form->getElement($hiddenKey)) {
                    $form->$hiddenKey->setValue($value);
                }
            }
        }
        
        // process the form if posted
        if ($this->getRequest()->isPost()) {            
            $uploadedFileNames = array();
            $elements = $form->getElements();
            foreach($elements as $element) {
                if ($element instanceof Zend_Form_Element_File) {
                    $elementName = $element->getName();
                    
                    // add filters to rename all of the uploaded theme files                                               
                    
                    // Make sure the file was uploaded before adding the Rename filter to the element
                    if ($element->isUploaded()) {
                        if (get_option('disable_default_file_validation') == '0') {
                            $element->addValidator(new Omeka_Validate_File_Extension());
                            $element->addValidator(new Omeka_Validate_File_MimeType());
                        }
                        
                        $fileName = basename($element->getFileName());
                        $uploadedFileName = 'exhibit_'.$exhibit->title.'_'.Theme::getUploadedFileName($themeName, $elementName, $fileName);                      
                        $uploadedFileNames[$elementName] = $uploadedFileName;
                        $uploadedFilePath = $element->getDestination() . DIRECTORY_SEPARATOR . $uploadedFileName;
                        $element->addFilter('Rename', array('target'=>$uploadedFilePath, 'overwrite'=>true));
                    }

                    // If file input's related  hidden input has a non-empty value, 
                    // then the user has NOT changed the file, so do NOT upload the file.
                    if ($hiddenFileElement = $form->getElement($hiddenFieldPrefix . $elementName)) { 
                        $hiddenFileElementValue = trim($_POST[$hiddenFileElement->getName()]); 
                        if ($hiddenFileElementValue != "") {                              
                            // Ignore the file input element
                            $element->setIgnore(true);
                        }
                    }
                }
            }

            // validate the form (note: this will populate the form with the post values)
            if ($form->isValid($_POST)) {                                
                $formValues = $form->getValues();
                $currentThemeOptions = $previousOptions;
                
                foreach($elements as $element) {
                    if ($element instanceof Zend_Form_Element_File) {                                                
                        $elementName = $element->getName();
                        // set the theme option for the uploaded file to the file name
                        if ($element->getIgnore()) {
                            // set the form value to the old theme option
                            $formValues[$elementName] = $currentThemeOptions[$elementName];
                        } else {                          
                            // set the new file
                            $newFileName = $uploadedFileNames[$elementName];
                            $formValues[$elementName] = $newFileName;
                            
                            // delete old file if it is not the same as the new file name
                            $oldFileName = $currentThemeOptions[$elementName];
                            if ($oldFileName != $newFileName) {
                                $oldFilePath = THEME_UPLOADS_DIR . DIRECTORY_SEPARATOR . $oldFileName;
                                if (is_writable($oldFilePath) && is_file($oldFilePath)) {
                                    unlink($oldFilePath);
                                }
                            }
                        }       
                    } else if ($element instanceof Zend_Form_Element_Hidden) {
                        $elementName = $element->getName();
                        // unset the values for the hidden fields associated with the file inputs
                        if (strpos($elementName, $hiddenFieldPrefix) == 0) { 
                            unset($formValues[$elementName]);
                        }
                   }
                }
                
                // unset the submit input
                unset($formValues['submit']);
                
                reset($formValues);
                                
                // set the theme options
                $exhibit->setThemeOptions($formValues);
                $exhibit->save();
                
                $this->flashSuccess('The theme settings were successfully saved!');
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
        
        $this->render('page-content-form');
    }

    public function editPageMetadataAction()
    {
        $exhibitPage = $this->findById(null,'ExhibitPage');
        $exhibitSection = $exhibitPage->Section;
        $exhibit = $exhibitSection->Exhibit;
        
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
        }
        return $success;
    }
    
    /**
     * 1st URL param = Section ID
     *
     **/
    public function editSectionAction()
    {
        $exhibitSection = $this->findById(null, 'ExhibitSection');
        $exhibit = $exhibitSection->Exhibit;
        return $this->processSectionForm($exhibitSection, 'Edit', $exhibit);
    }
    
    public function deleteSectionAction()
    {
        //Delete the section and re-order the rest of the sections in the exhibit
        
        $exhibitSection = $this->findById(null,'ExhibitSection');
        $exhibit = $exhibitSection->Exhibit;
                
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
    
    /////END AJAX-ONLY ACTIONS
}

class ExhibitsController_BadSlug_Exception extends Zend_Controller_Exception 
{    
}