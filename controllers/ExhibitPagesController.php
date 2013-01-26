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
}