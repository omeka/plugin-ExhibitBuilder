<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Controller for Exhibits.
 *
 * @package ExhibitBuilder
 */
class ExhibitBuilder_ExhibitsController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Exhibit');
        $this->_browseRecordsPerPage = 10;
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

            return $this->_renderExhibit(compact('exhibit', 'item'), 'item');
        } else {
            $this->_helper->flashMessenger(__('This item is not used within this exhibit.'), 'error');
            throw new Omeka_Controller_Exception_403;
        }
    }

    public function itemContainerAction()
    {
        $itemId = (int)$this->_getParam('item_id');
        $fileId = (int)$this->_getParam('file_id');
        $orderOnForm = (int)$this->_getParam('order_on_form');

        $item = $this->_helper->db->getTable('Item')->find($itemId);
        $file = $this->_helper->db->getTable('File')->find($fileId);

        $this->view->item = $item;
        $this->view->file = $file;
        $this->view->orderOnForm = $orderOnForm;
    }

    public function showAction()
    {
        $exhibit = $this->_findByExhibitSlug();
        
        if (!$exhibit) {
            throw new Omeka_Controller_Exception_404;
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
        $pageTable = $this->_helper->db->getTable('ExhibitPage');
        
        foreach ($params as $level=>$slug) {
            if (!empty($slug)) {
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
        for ($i=0 ; $i < count($parentPages) - 2; $i++) {
            $currPage = $parentPages[$i];
            $nextPage = $parentPages[$i + 1];
            if ($nextPage->parent_id != $currPage->id) {
                throw new Omeka_Controller_Exception_404;
            }
        }

        fire_plugin_hook('show_exhibit', array('exhibit' => $exhibit, 'exhibitPage' => $exhibitPage));

        $this->_renderExhibit(array(
            'exhibit' => $exhibit, 
            'parentPages' => $parentPages, 
            'exhibit_page' => $exhibitPage));
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
        $this->_renderExhibit(compact('exhibit'), 'summary');
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
        $form->removeDecorator('Form');

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
            } else {
                $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
            }
        }

        $this->view->assign(compact('exhibit', 'form', 'theme'));
    }

    protected function _findByExhibitSlug($exhibitSlug = null)
    {
        if (!$exhibitSlug) {
            $exhibitSlug = $this->_getParam('slug');
        }
        $exhibit = $this->_helper->db->getTable()->findBySlug($exhibitSlug);
        return $exhibit;
    }

    /**
     * Figure out how to render the exhibit.
     * 1) the view needs access to the shared directories
     * 2) if the exhibit has an associated theme, render the pages for that specific exhibit theme,
     *      otherwise display the generic theme pages in the main public theme
     *
     * @return void
     **/
    protected function _renderExhibit($vars, $toRender = 'show')
    {
        extract($vars);
        $this->view->assign($vars);

        /* If we don't pass a valid value to $toRender, thow an exception. */
        if (!in_array($toRender, array('show', 'summary', 'item'))) {
            throw new Exception( 'You gotta render some stuff because whatever!' );
        }
        return $this->render($toRender);

    }

    protected function _redirectAfterAdd($exhibit)
    {
        if (array_key_exists('add_page', $_POST)) {
            $this->_helper->redirector->gotoRoute(array('action' => 'add-page', 'id' => $exhibit->id), 'exhibitStandard');
        } else {
            $this->_helper->redirector->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
        }
    }

    protected function _redirectAfterEdit($exhibit)
    {
        $this->_redirectAfterAdd($exhibit);
    }
    
    /**
     * Return the delete confirm message for deleting a record.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return string
     */
    protected function _getDeleteConfirmMessage($record)
    {
        return __('This will delete the exhibit "%s" and its associated metadata, as well as all exhibit pages associated with this exhibit.' . 
                  '  However, it will NOT delete any associated items.', $record->title);
    }
    
    /**
     * Return the success message for editing a record.
     * 
     * Default is empty string. Subclasses should override it.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return string
     */
    protected function _getEditSuccessMessage($record)
    {
        return __('The exhibit "%s" was successfully changed!', $record->title);
    }
    
    /**
     * Return the success message for deleting a record.
     * 
     * Default is empty string. Subclasses should override it.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return string
     */
    protected function _getDeleteSuccessMessage($record)
    {
        return __('The exhibit "%s" was successfully deleted!', $record->title);
    }
    
    /**
     * Return the success message for adding a record.
     * 
     * Default is empty string. Subclasses should override it.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return string
     */
    protected function _getAddSuccessMessage($record)
    {
        return __('The exhibit "%s" was successfully added!', $record->title);
    }
}