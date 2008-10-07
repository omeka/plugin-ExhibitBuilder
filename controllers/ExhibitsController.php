<?php 
require_once 'Exhibit.php';
/**
 * @package Omeka
 **/
require_once 'Omeka/Controller/Action.php';
class ExhibitBuilder_ExhibitsController extends Omeka_Controller_Action
{
	protected $session;
	
	public function init()
	{
		$this->_modelClass = 'Exhibit';
		
		require_once 'Zend/Session.php';
		$this->session = new Zend_Session_Namespace('Exhibit');
	}
	
	public function indexAction() {}
	
	public function tagsAction()
	{
		$this->_forward('browse', 'Tags', null, array('tagType' => 'Exhibit', 'renderPage'=>'exhibits/tags.php'));
	}
	
	public function browseAction()
	{
		$filter = array();
		
		if(($tags = $this->_getParam('tag')) || ($tags = $this->_getParam('tags'))) {
			$filter['tags'] = $tags;
		}
				
		$exhibits = $this->_table->findBy($filter);
				
		Zend_Registry::set('exhibits', $exhibits);
		
		fire_plugin_hook('browse_exhibits', $exhibits);
		
		$this->view->assign(compact('exhibits'));
	}
	
	public function showitemAction()
	{
		$item_id = $this->_getParam('item_id');
		$slug = $this->_getParam('slug');
		
		$exhibit = is_numeric($slug) ?
			$this->_table->find($slug) :
			$this->_table->findBySlug($slug);
			
		$exhibittable = $this->_table;
		
		$item = $this->findById($item_id, 'Item');	
		
		$section_name = $this->_getParam('section');
		$section = $exhibit->getSection($section_name);

		if( $item and $this->_table->exhibitHasItem($exhibit, $item) ) {
			
			Zend_Registry::set('item', $item);

			Zend_Registry::set('exhibit', $exhibit);

			Zend_Registry::set('section', $section);
			
			//Plugin hooks
			fire_plugin_hook('show_exhibit_item',  $item, $exhibit);
			
			return $this->renderExhibit(compact('exhibit','item', 'section'), 'item');
		}else {
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
		
		if($section) {
			$pageOrder = $this->_getParam('page');

			$page = $section->getPage($pageOrder);			
		}else {
		    $section = $exhibit->getFirstSection();
		}
		
		$layout = $page->layout;

/*
			if(!$section) {
			$this->flash('This section does not exist for this exhibit.');
		}
		elseif(!$page) {
			$this->flash('This page does not exist in this section of the exhibit.');
		}
*/	
		
		//Register these so that theme functions can use them
		Zend_Registry::set('section',	$section);
		Zend_Registry::set('exhibit',	$exhibit);
		Zend_Registry::set('page',		$page);
		
		fire_plugin_hook('show_exhibit', $exhibit,$section,$page);
                
		$this->renderExhibit(compact('section','exhibit','page'));
	}
	
	protected function findBySlug($slug=null) 
	{
		if(!$slug) {
			$slug = $this->_getParam('slug');
		}
		
		//Slug can be either the numeric 'id' for the exhibit or the alphanumeric slug
		if(is_numeric($slug)) {
			$exhibit = $this->_table->findById($slug);
		}else {
			$exhibit = $this->_table->findBySlug($slug);
		}
		
		if(!$exhibit) {
		    throw new Zend_Controller_Exception('Cannot find exhibit with slug: '. $slug);
		}
				
		return $exhibit;
	}
	
	public function summaryAction()
	{
		$exhibit = $this->findBySlug();		
				
		Zend_Registry::set('exhibit', $exhibit);

		fire_plugin_hook('show_exhibit', $exhibit);
		
		$this->renderExhibit(compact('exhibit'), 'summary');
	}
	
	/**
	 * Figure out how to render the exhibit.  
	 * 1) the view needs access to the shared directories
	 * 2) if the exhibit has an associated theme, render the pages for that specific exhibit theme, 
	 *		otherwise display the generic theme pages in the main public theme
	 * 
	 * @return void
	 **/
	protected function renderExhibit($vars, $toRender='show') {
		/* 	If there is a theme, render the header/footer and layout page,
			Otherwise render the default exhibits/show.php page
		*/
		extract($vars);
		
		$this->view->assign($vars);
		
		if(!empty($exhibit->theme)) {
					
			//Hack to get just the directory name for the exhibit themes
            $exhibitThemesDir = 'exhibit_themes';
            
			switch ($toRender) {
				case 'show':
					$renderPath = $exhibitThemesDir.DIRECTORY_SEPARATOR.$exhibit->theme.DIRECTORY_SEPARATOR.'show.php';
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

                // throw new Exception( 
                //  "Exhibit theme named '$exhibit->theme' no longer exists!\n\n  
                //  Please change the exhibit's theme in order to properly view the exhibit." );
			
		}else {
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
		$exhibit = $this->findById();
		return $this->processExhibitForm($exhibit, 'Edit');
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

			if($retVal) {
				if(array_key_exists('add_section',$_POST)) {
					//forward to addSection & unset the POST vars 
					unset($_POST);
					$this->redirect->goto('add-section', null, null, array('id'=>$exhibit->id) );
					return;
				}elseif(array_key_exists('save_exhibit', $_POST)) {
				
					$this->redirect->goto('browse');
				}else {
				
					//Everything else should render the page
					//return $this->render('exhibits/form/exhibit.php',compact('exhibit'));
				}			
			}
					
		} 
		catch (Omeka_Validator_Exception $e) {
			$this->flashValidationErrors($e);
			$this->view->flash = $e->getMessage();
		}
		catch (Exception $e) {
			$this->flash($e->getMessage());
			$this->view->flash = $e->getMessage(); 
		}
		
		// $this->view->exhibit = $exhibit;
		$this->view->assign(compact('exhibit', 'actionName'));
		
		//Send a header that will inform us that the request was a failure
		//@see http://tech.groups.yahoo.com/group/rest-discuss/message/6183
		if (!$retVal) {
		  $this->getResponse()->setHttpResponseCode(422);
		}
		
		//@duplication see ExhibitsController::processSectionForm()
		//If the form submission was invalid 
		if(!$this->getRequest()->isXmlHttpRequest()) {
			$this->render('exhibit-form');
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
		//Check for a 'cancel' button so we can redirect
		if(isset($_POST['cancel_section'])) {
			$this->redirect->goto('edit', null, null, array('id' => $section->exhibit_id));
		}
		
		$retVal = false;
		
		try {
			//Section form may be prefixed with Section (like name="Section[title]") or it may not be, depending
			
			if(array_key_exists('Section', $_POST)) {
				$toPost = $_POST['Section'];
			}else {
				$toPost = $_POST;
			}
			
			$retVal = $section->saveForm($toPost);
		} 
		catch (Omeka_Validator_Exception $e) {
			$this->flashValidationErrors($e);
		}
		catch (Exception $e) {
			$this->flash($e->getMessage());
		}
					
		//If successful form submission
		if($retVal)
		{	
			$this->flashSuccess("Changes to the exhibit's section were saved successfully!");
			//Forward around based on what submit button was pressed
			
			if(array_key_exists('exhibit_form',$_POST)) {
				
				//Forward to the 'edit' action
				$this->redirect->goto('edit', null, null, array('id'=>$section->exhibit_id));
				return;
			
			}elseif(array_key_exists('page_form',$_POST)) {
				
				//Forward to the addPage action (id is the section id)
				$this->redirect->goto('add-page', null, null, array('id'=>$section->id));
				return;
				
			}elseif(array_key_exists('add_new_section', $_POST)) {
				//Forward back to adding a new section to the exhibit
				$this->redirect->goto('add-section', null, null, array('id'=>$section->Exhibit->id));
			}
		}

		$this->view->assign(compact('exhibit', 'section', 'actionName'));
		
		if ($_POST and !$retVal) {
            //Send a header that will inform us that the request was a failure
            //@see http://tech.groups.yahoo.com/group/rest-discuss/message/6183
		  $this->getResponse()->setHttpResponseCode(422);
		}
		
		// Render the big section form script if this is not an AJAX request.
		if (!$this->getRequest()->isXmlHttpRequest() ) {
		    $this->render('section-form');	
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
				
		if(isset($_POST['cancel'])) {
			$this->setLayout(null);
			$this->redirect->goto('edit-section', null, null, array('id'=>$section->id));
		}
		
		//Check to see if the page var was saved in the session
		if ($layout = $this->getLayout()) {
			$page = new ExhibitPage;
			$page->layout = $layout;						
		} else {
			$page = new ExhibitPage;
		}
		$page->section_id = $section->id;		
				
		//Set the order for the new page
		$numPages = $section->getPageCount();
		$page->order = $numPages + 1;		
		
		return $this->processPageForm($page, 'Add', $section, $exhibit);
	}
	
	protected function getLayout()
	{
		return $this->session->layout;
	}
	
	protected function setLayout($layout)
	{
		$this->session->layout = (string) $layout;
	}
		
	protected function processPageForm($page, $actionName, $section=null, $exhibit=null) 
	{
		//'cancel_and_section_form' and 'cancel_and_exhibit_form' as POST elements will cancel adding a page
		//And they will redirect to whatever form is important
		if(isset($_POST['cancel_and_section_form'])) {
			$this->redirect->goto('edit-section', null, null, array('id'=>$page->section_id));
		}
		
		if(isset($_POST['cancel_and_exhibit_form'])) {
			$this->redirect->goto('edit', null, null, array('id'=>$section->exhibit_id));
		}
		
		//Register the page var so that theme functions can use it
		Zend_Registry::set('page', $page);

		$this->view->assign(compact('exhibit', 'section','page', 'actionName'));
				
		if (!empty($_POST)) {

			if (array_key_exists('choose_layout', $_POST)) {
			
				//A layout has been chosen for the page
				$this->setLayout($_POST['layout']);
				
				$page->layout = (string) $_POST['layout'];
				
				return $this->render('page-form');
			
			} elseif (array_key_exists('change_layout', $_POST)) {
				
				//User wishes to change the current layout
				
				//Reset the layout vars
				$this->setLayout(null);
				$page->layout = null;
				
				return $this->render('layout-form');		
			}
				
			else {
				try {
					
					if($layout = $this->getLayout()) {
						$page->layout = $layout;
					}

					$retVal = $page->saveForm($_POST);

				} catch (Exception $e) {
					$this->flash($e->getMessage());
				}

				//Otherwise the page form has been submitted
				if($retVal) {
				
					//Unset the page var that was saved in the session
					$this->setLayout(null);
				
				
					if(array_key_exists('exhibit_form', $_POST)) {
					
						//Return to the exhibit form
						$this->redirect->goto('edit', null, null, array('id'=>$section->Exhibit->id));
						return;
					
					}elseif(array_key_exists('section_form', $_POST)) {
					
						//Return to the section form
						$this->redirect->goto('edit-section', null, null, array('id'=>$section->id));
						return;
					
					}elseif(array_key_exists('page_form', $_POST)) {
					
						//Add another page
						$this->redirect->goto('add-page', null, null, array('id'=>$section->id));
						return;
					
					}elseif(array_key_exists('save_and_paginate', $_POST)) {
				
						//User wants to save the current set of pagination
						//@todo How would this work?
						$paginationPage = $this->_getParam('page');
						
						$this->redirect->goto('edit-page', null, null, array('id'=>$page->id, 'page'=>$paginationPage) );
						return;
						
					}
				}
			}
		}
		
		if ( empty($page->layout) ) {
			$this->render('layout-form');
		}else {
			$this->render('page-form');	
		}		
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
	
	public function editPageAction()
	{
		$page = $this->findById(null,'ExhibitPage');
		$section = $page->Section;
		$exhibit = $section->Exhibit;
		
		return $this->processPageForm($page, 'Edit', $section, $exhibit);
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
	
	public function sectionFormAction()
	{
		$exhibit = $this->findById();
		
		$section = new ExhibitSection;
		$section->Exhibit = $exhibit;
		
		$this->view->section = $section;
		$this->render('sectionform');
	}
	
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

	/**
	 * Return the Exhibit ID as a JSON value
	 *
	 * @return void
	 **/
	public function saveAction()
	{
		//Run a permission check
		if(!$this->isAllowed('add')) {
			$this->forbiddenAction();
		}
		
		if(!empty($_POST)) {
			$exhibit = $this->findOrNew();
			
			require_once 'Zend/Json.php';
			$return = array();
			try {
				$exhibit->saveForm($_POST);
			} catch (Omeka_Validator_Exception $e) {
				//Set the 404 response code
				$this->getResponse()->setHttpResponseCode(422); 
				
				$this->flashValidationErrors($e);
			}
			
			$this->view->exhibit = $exhibit;			
		}
	}
	
	/////END AJAX-ONLY ACTIONS
	
	/**
	 * The route exhibits/whatever can be one of three things: 
	 *	built-in controller action
	 *	static page
	 *	exhibit slug
	 *
	 *	Unfortunately we have no way of knowing which one it is without a complicated database/filesystem check,
	 *	so it can't go in the routes file (at least not in any way I've been able to figure out) -- Kris
	 * 
	 * @return void
	 **/
	public function routeSimpleAction()
	{
		//Check if it is a built in controller action
		$slug = strtolower($this->_getParam('slug'));
		
		$action = $slug . 'Action';
		
		if(method_exists($this, $action)) {
			$this->_setParam('action', $slug);
			return $this->_forward($slug, 'exhibits', null, $this->_getAllParams());
			exit;
		}
		
		//Check if it is a static page
		$page = $slug . '.php';
		
		//Try to render, invalid pages will be caught as exceptions
		try {
			return $this->render('exhibits' . DIRECTORY_SEPARATOR . $page);
		} catch (Zend_View_Exception $e) {}
		
		
		//Otherwise this is a slug for an Exhibit
		
		$this->_forward('summary', 'exhibits', null, $this->_getAllParams());
	}
} 
?>
