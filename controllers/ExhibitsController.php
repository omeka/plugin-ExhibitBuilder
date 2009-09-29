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
    
    public function tagsAction()
    {
        $params = array_merge($this->_getAllParams(), array('type'=>'Exhibit'));
        $tags = $this->getTable('Tag')->findBy($params);
        $this->view->assign(compact('tags'));
    }
    
    public function showitemAction()
    {
        $item_id = $this->_getParam('item_id');
        $slug = $this->_getParam('slug');

        $exhibit = $this->_table->findBySlug($slug);
 
        $exhibittable = $this->_table;

        $item = $this->findById($item_id, 'Item');   

        $section_name = $this->_getParam('section');
        $section = $exhibit->getSection($section_name);
  
        if ($item and $this->_table->exhibitHasItem($exhibit->id, $item->id) ) {
     
            Zend_Registry::set('exhibit_builder_item', $item);
            Zend_Registry::set('exhibit_builder_exhibit', $exhibit);
            Zend_Registry::set('exhibit_builder_section', $section);
     
            //Plugin hooks
            fire_plugin_hook('show_exhibit_item',  $item, $exhibit);
     
            return $this->renderExhibit(compact('exhibit','item', 'section'), 'item');
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
    
    public function showAction()
    {                  
        $exhibit = $this->findBySlug();
                
        $sectionSlug = $this->_getParam('section');

        $section = $exhibit->getSection($sectionSlug);
        
        if ($section) {
            $pageSlug = $this->_getParam('page');
            $page = $section->getPageBySlug($pageSlug);
            if ($page == null) {
                $page = $section->getPageByOrder(1);
            }
        } else {
            $section = $exhibit->getFirstSection();
        }
        
        $layout = $page->layout;
        
        //Register these so that theme functions can use them
        Zend_Registry::set('exhibit_builder_section', $section);
        Zend_Registry::set('exhibit_builder_exhibit', $exhibit);
        Zend_Registry::set('exhibit_builder_page', $page);
        
        fire_plugin_hook('show_exhibit', $exhibit,$section,$page);
                
        $this->renderExhibit(compact('section','exhibit','page'));
    }
    
    protected function findBySlug($slug=null) 
    {
        if (!$slug) {
            $slug = $this->_getParam('slug');
        }
        $exhibit = $this->_table->findBySlug($slug);
        if (!$exhibit) {
            throw new Zend_Controller_Exception('Cannot find exhibit with slug: '. $slug);
        }
                
        return $exhibit;
    }
    
    public function summaryAction()
    {
        $exhibit = $this->findBySlug();     
        Zend_Registry::set('exhibit_builder_exhibit', $exhibit);
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
    protected function renderExhibit($vars, $toRender='show') 
    {
        /*  If there is a theme, render the header/footer and layout page,
            Otherwise render the default exhibits/show.php page
        */
        extract($vars);
        $this->view->assign($vars);
        if (!empty($exhibit->theme)) {
                    
            //Hack to get just the directory name for the exhibit themes
            $exhibitThemesDir = EXHIBIT_THEMES_DIR_NAME;  
            switch ($toRender) {
                case 'show':
                    if ($section->hasPages()) {
                        $renderPath = $exhibitThemesDir.DIRECTORY_SEPARATOR.$exhibit->theme.DIRECTORY_SEPARATOR.'show.php';
                    } else { 
                        throw new Exception('This section has no pages!');
                    }
                    break;
                case 'summary':
                    $renderPath = $exhibitThemesDir. DIRECTORY_SEPARATOR . $exhibit->theme . DIRECTORY_SEPARATOR . 'summary.php';
                    break;
                case 'item':
                    $renderPath = $exhibitThemesDir.DIRECTORY_SEPARATOR.$exhibit->theme.DIRECTORY_SEPARATOR.'item.php';
                    break;
                default:
                    throw new Exception( 'Hey, you gotta render something!' );
                    break;
            }
            return $this->renderScript($renderPath);
            
        } else {
            if (!in_array($toRender, array('show', 'summary', 'item'))) {
                throw new Exception( 'You gotta render some stuff because whatever!' );
            }
            return $this->render($toRender);
        }
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

        $this->view->assign(compact('exhibit', 'actionName'));
                
        //@duplication see ExhibitsController::processSectionForm()
        //If the form submission was invalid 
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->render('exhibit-metadata-form');
        }
    }
    
    /**
     * 1st URL param = 'id' for Exhibit
     *
     **/
    public function addSectionAction()
    {
        $exhibit = $this->findById();
        $section = new ExhibitSection;
        $section->exhibit_id = $exhibit->id;
        
        //Give the new section a section order (1, 2, 3, ...)
        $numSections = $exhibit->getSectionCount();
        $section->order = $numSections + 1;
        
        //Tell the plugin hook that we are adding a section
        $this->addSection = true;
        
        return $this->processSectionForm($section, 'Add', $exhibit);
    }
    
    protected function processSectionForm($section, $actionName, $exhibit=null)
    {       
        $retVal = false;
        
        try {
            //Section form may be prefixed with Section (like name="Section[title]") or it may not be, depending
            
            if (array_key_exists('Section', $_POST)) {
                $toPost = $_POST['Section'];
            } else {
                $toPost = $_POST;
            }
            $retVal = $section->saveForm($toPost);
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        } catch (Exception $e) {
            $this->flash($e->getMessage());
        }
                    
        //If successful form submission
        if ($retVal) {   
            $this->flashSuccess("Changes to the exhibit's section were saved successfully!");
            
            //Forward around based on what submit button was pressed
            if (array_key_exists('page_form',$_POST)) {
                
                //Forward to the addPage action (id is the section id)
                $this->redirect->goto('add-page', null, null, array('id'=>$section->id));
                return;
                
            } elseif(array_key_exists('section_form', $_POST)) {
                $this->redirect->goto('edit-section', null, null, array('id'=>$section->id));
            }
        }

        $this->view->assign(compact('exhibit', 'section', 'actionName'));
                
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
        $section = $this->findById(null,'ExhibitSection');
        $exhibit = $section->Exhibit;
                
        $page = new ExhibitPage;
        $page->section_id = $section->id;       
                
        //Set the order for the new page
        $numPages = $section->getPageCount();
        $page->order = $numPages + 1;       
        
        $success = $this->processPageForm($page, 'Add', $section, $exhibit);
        if ($success) {
            return $this->redirect->goto('edit-page-content', null, null, array('id'=>$page->id));
        }

        $this->render('page-metadata-form');
    }
    
    public function editPageContentAction()
    {
        $page = $this->findById(null,'ExhibitPage');
        $section = $page->Section;
        $exhibit = $section->Exhibit;

        $success = $this->processPageForm($page, 'Edit', $section, $exhibit);
        
        
        if ($success and array_key_exists('section_form', $_POST)) {
            //Return to the section form
            return $this->redirect->goto('edit-section', null, null, array('id'=>$section->id));
        } else if ($success and array_key_exists('page_metadata_form', $_POST)) {
           return $this->redirect->goto('edit-page-metadata', null, null, array('id'=>$page->id));
        } else if (array_key_exists('page_form',$_POST)) {
            //Forward to the addPage action (id is the section id)
            return $this->redirect->goto('add-page', null, null, array('id'=>$page->Section->id));
        }
        
        $this->render('page-content-form');
    }

    public function editPageMetadataAction()
    {
        $page = $this->findById(null,'ExhibitPage');
        $section = $page->Section;
        $exhibit = $section->Exhibit;
        
        $success = $this->processPageForm($page, 'Edit', $section, $exhibit);
        
        if ($success) {
            return $this->redirect->goto('edit-page-content', null, null, array('id'=>$page->id));
        }
        
        $this->render('page-metadata-form');
    }

    protected function processPageForm($page, $actionName, $section=null, $exhibit=null) 
    {       
        //Register the page var so that theme functions can use it
        Zend_Registry::set('exhibit_builder_page', $page);

        $this->view->assign(compact('exhibit', 'section','page', 'actionName'));        
        if (!empty($_POST)) {
            try {
                $success = $page->saveForm($_POST);
            } catch (Exception $e) {
                $this->flash($e->getMessage());
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
        $section = $this->findById(null, 'ExhibitSection');
        $exhibit = $section->Exhibit;
        return $this->processSectionForm($section, 'Edit', $exhibit);
    }
    
    public function deleteSectionAction()
    {
        //Delete the section and re-order the rest of the sections in the exhibit
        
        $section = $this->findById(null,'ExhibitSection');
        $exhibit = $section->Exhibit;
                
        $section->delete();
        
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
        $page = $this->findById(null,'ExhibitPage');
        $section = $page->Section;
                
        $page->delete();
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->view->section = $section;
            $this->render('page-list');
        } else {
            $this->redirect->goto('edit-section', null, null, array('id' => $section->id) );
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
        $this->view->section = $this->findById(null, 'ExhibitSection');
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