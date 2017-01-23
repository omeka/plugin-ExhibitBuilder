<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

class ExhibitBuilder_FilesController extends Omeka_Controller_AbstractActionController
{
    /*
     * AJAX partial for the cover image section of the Exhibit metadata form.
     */
    public function coverImageAction()
    {
        $file = $this->_helper->db->findById(null, 'File');
        $this->view->file = $file;
    }
}
