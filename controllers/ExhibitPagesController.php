<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Controller for Exhibit Pages.
 *
 * @package ExhibitBuilder
 */
class ExhibitBuilder_ExhibitPagesController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ExhibitPage');
        $this->_browseRecordsPerPage = 10;
    }
    
    /**
     * Add a page to an exhibit
     *
     * 1st URL param = 'id' for the exhibit that will contain the page
     *
     **/
    public function addAction()
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
        if ($previousPageId) {
            //set the order to be right after the previous one. Page's beforeSave method will bump up later page orders as needed
            $previousPage = $db->getTable('ExhibitPage')->find($previousPageId);
            $exhibitPage->parent_id = $previousPage->parent_id;
            $exhibitPage->order = $previousPage->order + 1;
        } else {
            $childCount = $exhibit->countTopPages();
            $exhibitPage->order = $childCount +1;
        }

        $success = $this->_processPageForm($exhibitPage, 'Add', $exhibit);
        if ($success) {
            $this->_helper->flashMessenger(__('The exhibit page "%s" was successfully added!', $exhibitPage->title), 'success');
            return $this->_helper->redirector('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }

    public function editAction()
    {
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');
        $this->_forward('edit-page-content', 'exhibit-pages', null, array('id' => $exhibitPage->id));
    }

    public function editPageContentAction()
    {
        $db = $this->_helper->db->getDb();        
        
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');
        $exhibit = $db->getTable('Exhibit')->find($exhibitPage->exhibit_id);

        if (!$this->_helper->acl->isAllowed('edit', $exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $layoutIni = $this->_layoutIni($exhibitPage->layout);
        $layoutName = $layoutIni->name;
        $layoutDescription = $layoutIni->description;

        $success = $this->_processPageForm($exhibitPage, 'Edit', $exhibit);
        
        if ($success) {
            if (array_key_exists('page_metadata_form', $_POST)) {
                $this->_helper->flashMessenger(__('The exhibit page "%s" was successfully changed!', $exhibitPage->title), 'success');
                return $this->_helper->redirector('edit-page-metadata', null, null, array('id'=>$exhibitPage->id));
            } else if (array_key_exists('page_form',$_POST)) {
                $this->_helper->flashMessenger(__('The exhibit page "%s" was successfully changed!', $exhibitPage->title), 'success');
                //Forward to the addPage action (id is the exhibit)
                return $this->_helper->redirector('add', null, null, array('id' => $exhibitPage->exhibit_id, 'previous' => $exhibitPage->id));
            }
        }

        $this->view->layoutName = $layoutName;
        $this->view->layoutDescription = $layoutDescription;

        $this->render('page-content-form');
    }

    public function editPageMetadataAction()
    {
        $exhibitPage = $this->_helper->db->findById(null,'ExhibitPage');

        $exhibit = $exhibitPage->getExhibit();

        if (!$this->_helper->acl->isAllowed('edit', $exhibit)) {
            throw new Omeka_Controller_Exception_403;
        }

        $success = $this->_processPageForm($exhibitPage, 'Edit', $exhibit);

        if ($success) {
            return $this->_helper->redirector('edit-page-content', null, null, array('id'=>$exhibitPage->id));
        }

        $this->render('page-metadata-form');
    }
    
    protected function _processPageForm($exhibitPage, $actionName, $exhibit = null)
    {
        $this->view->assign(compact('exhibit', 'actionName'));
        $this->view->exhibit_page = $exhibitPage;
        if ($this->getRequest()->isPost()) {
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
    
    protected function _layoutIni($layout)
    {
        $iniPath = EXHIBIT_LAYOUTS_DIR . DIRECTORY_SEPARATOR. "$layout" . DIRECTORY_SEPARATOR . "layout.ini";
        if (file_exists($iniPath) && is_readable($iniPath)) {
            $ini = new Zend_Config_Ini($iniPath, 'layout');
            return $ini;
        }
        return false;
    }
    
    /**
     * Redirect to edit exhibit after an exhibit page from that exhibit is successfully deleted.
     *
     * @param ExhibitBuilder_ExhibitPage $record
     */
    protected function _redirectAfterDelete($record)
    {
        $exhibit = $record->getExhibit();
        $this->_helper->flashMessenger(__('The exhibit page "%s" was successfully deleted from the "%s" exhibit.', $record->title, $exhibit->title), 'success');
        $this->_forward('edit', 'exhibits', null, array('id' => $exhibit->id));        
    }
    
    /**
     * Return the delete confirm message for deleting a record.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return string
     */
    protected function _getDeleteConfirmMessage($record)
    {
        return __('This will delete the exhibit page "%s" and its associated metadata from the "%s" exhibit.' . 
                  '  However, it will NOT delete any associated items.', $record->title, $record->getExhibit()->title);
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
        return __('The exhibit page "%s" was successfully changed!', $record->title);
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
        return __('The exhibit page "%s" was successfully deleted!', $record->title);
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
        return __('The exhibit page "%s" was successfully added!', $record->title);
    }
}