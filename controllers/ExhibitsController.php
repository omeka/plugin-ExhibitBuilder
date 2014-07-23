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
    protected $_autoCsrfProtection = true;

    /**
     * Controller-wide initialization. Sets the underlying model to use.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Exhibit');
    }

    /**
     * Use global settings for determining browse page limits.
     *
     * @return int
     */
    public function _getBrowseRecordsPerPage()
    {
        if (is_admin_theme()) {
            return (int) get_option('per_page_admin');
        } else {
            return (int) get_option('per_page_public');
        }
    }

    /**
     * Browse exhibits action.
     */
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

    /**
     * Find an exhibit by its slug.
     *
     * @param string|null $exhibitSlug The slug to look up. If null, look up
     *  the slug from the current request.
     * @return Exhibit
     */
    protected function _findByExhibitSlug($exhibitSlug = null)
    {
        if (!$exhibitSlug) {
            $exhibitSlug = $this->_getParam('slug');
        }
        $exhibit = $this->_helper->db->getTable()->findBySlug($exhibitSlug);
        return $exhibit;
    }

    /**
     * List tags for exhibits action.
     */
    public function tagsAction()
    {
        $params = array_merge($this->_getAllParams(), array('type'=>'Exhibit'));
        $tags = $this->_helper->db->getTable('Tag')->findBy($params);
        $this->view->assign(compact('tags'));
    }

    /**
     * Show item in exhibit action.
     */
    public function showItemAction()
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
            $this->view->exhibit = $exhibit;
            $this->_forward('show', 'items', 'default', array('id' => $itemId));
        } else {
            throw new Omeka_Controller_Exception_403(__('This item is not used within this exhibit.'));
        }
    }

    /**
     * Show a single page of an exhibit.
     */
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

        $pageTable = $this->_helper->db->getTable('ExhibitPage');

        $parentPage = null;
        foreach($params as $slug) {
            if(!empty($slug)) {
                $exhibitPage = $pageTable->findBySlug($slug, $exhibit, $parentPage);
                if($exhibitPage) {
                    $parentPage = $exhibitPage;
                } else {
                    throw new Omeka_Controller_Exception_404;
                }
            }
        }

        fire_plugin_hook('show_exhibit', array(
            'exhibit' => $exhibit,
            'exhibitPage' => $exhibitPage
        ));

        $this->view->assign(array(
            'exhibit' => $exhibit,
            'exhibit_page' => $exhibitPage,
        ));
    }

    /**
     * Show the summary page for an exhibit.
     */
    public function summaryAction()
    {
        $exhibit = $this->_findByExhibitSlug();
        if (!$exhibit) {
            throw new Omeka_Controller_Exception_404;
        }

        fire_plugin_hook('show_exhibit', array('exhibit' => $exhibit));
        $this->view->exhibit = $exhibit;
    }

    /**
     * Custom redirect for addAction allowing a page to be added immediately.
     *
     * @param Exhibit $exhibit
     */
    protected function _redirectAfterAdd($exhibit)
    {
        if (array_key_exists('add_page', $_POST)) {
            $this->_helper->redirector->gotoRoute(array('action' => 'add-page', 'id' => $exhibit->id), 'exhibitStandard');
        } else if (array_key_exists('configure-theme', $_POST)) {
            $this->_helper->redirector->gotoRoute(array('action' => 'theme-config', 'id' => $exhibit->id), 'exhibitStandard');
        } else {
            $this->_helper->redirector->gotoRoute(array('action' => 'edit', 'id' => $exhibit->id), 'exhibitStandard');
        }
    }

    /**
     * Custom redirect for editAction.
     *
     * @see _redirectAfterAdd
     * @param Exhibit $exhibit
     */
    protected function _redirectAfterEdit($exhibit)
    {
        $this->_redirectAfterAdd($exhibit);
    }

    /**
     * Theme configuration page for an exhibit.
     */
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


    /**
     * Add a page to an exhibit.
     *
     * The URL param 'id' refers to the exhibit that will contain the page, and
     * 'previous' refers to an existing page the new one will be placed after.
     */
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
            $childCount = $exhibit->countPages(true);
            $exhibitPage->order = $childCount +1;
        }

        $success = $this->processPageForm($exhibitPage, 'Add', $exhibit);
        if ($success) {
            $this->_helper->flashMessenger("Changes to the exhibit's page were successfully saved!", 'success');
            if (array_key_exists('add-another-page', $_POST)) {
                $this->_helper->redirector->gotoRoute(array('action' => 'add-page', 'id' => $exhibit->id, 'previous' => $exhibitPage->id), 'exhibitStandard');
            } else {
                $this->_helper->redirector->gotoRoute(array('action' => 'edit-page', 'id' => $exhibitPage->id), 'exhibitStandard');
            }
            return;
        }

        $this->render('page-form');
    }

    /**
     * Edit an existing exhibit page.
     */
    public function editPageAction()
    {
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');

        $exhibit = $exhibitPage->getExhibit();

        if (!$this->_helper->acl->isAllowed('edit', $exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $success = $this->processPageForm($exhibitPage, 'Edit', $exhibit);
        if ($success) {
            $this->_helper->flashMessenger("Changes to the exhibit's page were successfully saved!", 'success');
            if (array_key_exists('add-another-page', $_POST)) {
                $this->_helper->redirector->gotoRoute(array('action' => 'add-page', 'id' => $exhibit->id, 'previous' => $exhibitPage->id), 'exhibitStandard');
            } else {
                $this->_helper->redirector->gotoRoute(array('action' => 'edit-page', 'id' => $exhibitPage->id), 'exhibitStandard');
            }
            return;
        }

        $this->render('page-form');
    }

    /**
     * Handle the POST for the page add and edit actions.
     *
     * @param ExhibitPage $exhibitPage
     * @param string $actionName
     * @param Exhibit $exhibit
     */
    protected function processPageForm($exhibitPage, $actionName, $exhibit = null)
    {
        if (class_exists('Omeka_Form_SessionCsrf')) {
            $csrf = new Omeka_Form_SessionCsrf;
        } else {
            $csrf = '';
        }
        $this->view->assign(compact('exhibit', 'actionName'));
        $this->view->exhibit_page = $exhibitPage;
        $this->view->csrf = $csrf;
        if ($this->getRequest()->isPost()) {
            if (!($csrf === '' || $csrf->isValid($_POST))) {
                $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
                return;
            }

            $exhibitPage->setPostData($_POST);
            try {
                $success = $exhibitPage->save();
                return true;
            } catch (Exception $e) {
                $this->_helper->flashMessenger($e->getMessage(), 'error');
                return false;
            }
        }
    }

    /**
     * AJAX action for checking exhibit page data.
     */
    public function validatePageAction()
    {
        try {
            $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');
        } catch (Exception $e) {
            $exhibitPage = new ExhibitPage;
            if (($exhibit_id = $this->getParam('exhibit_id'))) {
                $exhibitPage->exhibit_id = $exhibit_id;
            }
            if (($parent_id = $this->getParam('parent_id'))) {
                $exhibitPage->parent_id = $parent_id;
            }
        }

        $exhibitPage->setPostData($_POST);
        $exhibitPage->validateSlug();
        if ($exhibitPage->isValid()) {
            $data = array('success' => true);
        } else {
            $data = array(
                'success' => false,
                'messages' => $exhibitPage->getErrors()->get()
            );
        }

        $this->_helper->json($data);
    }

    /**
     * AJAX/partial form for a single block in an page.
     */
    public function blockFormAction()
    {
        $block = new ExhibitPageBlock;
        $block->layout = $this->getParam('layout');
        $block->order = $this->getParam('order');

        $this->view->block = $block;
    }

    /**
     * AJAX/partial form for a single attachment on a block.
     */
    public function attachmentAction()
    {
        $attachment = new ExhibitBlockAttachment;
        $attachment->item_id = $this->_getParam('item_id');
        $attachment->file_id = $this->_getParam('file_id');
        $attachment->caption = $this->_getParam('caption');

        $block = new ExhibitPageBlock;
        $block->order = $this->_getParam('block_index');

        $this->view->attachment = $attachment;
        $this->view->block = $block;
        $this->view->index = (int) $this->_getParam('index');
    }

    /**
     * AJAX form for editing an attachment.
     */
    public function attachmentItemOptionsAction()
    {
        $attachment = new ExhibitBlockAttachment;
        $attachment->item_id = $this->_getParam('item_id');
        $attachment->file_id = $this->_getParam('file_id');
        $this->view->attachment = $attachment;
    }
}
