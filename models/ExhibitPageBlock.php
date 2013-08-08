<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPageBlock model.
 * 
 * @package ExhibitBuilder
 */
class ExhibitPageBlock extends Omeka_Record_AbstractRecord
{
    /**
     * ID of the page that owns this block.
     *
     * @var integer
     */
    public $page_id;

    /**
     * Identifier of the layout being used by this block.
     *
     * @var string
     */
    public $layout;

    /**
     * JSON-encoded set of options for the layout.
     *
     * @var string
     */
    public $options;

    /**
     * User-input text for this block, in HTML.
     *
     * @var string
     */
    public $text;

    /**
     * Order of this block on the page.
     *
     * @var integer
     */
    public $order;

    /**
     * Related record mappings.
     *
     * @var array
     */
    protected $_related = array('ExhibitBlockAttachment' => 'getAttachments');

    /**
     * Delete all attachments when deleting the block.
     */
    protected function _delete()
    {
        if ($this->ExhibitBlockAttachment) {
            foreach ($this->ExhibitBlockAttachment as $attachment) {
                $attachment->delete();
            }
        }
    }

    /**
     * Get the page that owns the block.
     *
     * @return ExhibitPage
     */
    public function getPage()
    {
        return $this->getTable('ExhibitPage')->find($this->page_id);
    }

    /**
     * Set the data for this block from an array.
     *
     * @param array $data Data to set
     */
    public function setData($data)
    {
        if (!empty($data['layout'])) {
            $this->layout = $data['layout'];
        }

        if (!empty($data['options'])) {
            $this->setOptions($data['options']);
        }

        if (!empty($data['text'])) {
            $this->text = $data['text'];
        } else {
            $this->text = null;
        }

        if (!empty($data['attachments'])) {
            $this->setAttachments($data['attachments']);
        } else {
            $this->setAttachments(array());
        }

        if (!empty($data['order'])) {
            $this->order = $data['order'];
        }
    }

    /**
     * Get a PHP array from the JSON-serialized layout options.
     *
     * @return array
     */
    public function getOptions()
    {
        if (!empty($this->options)) {
            return json_decode($this->options, true);
        } else {
            return array();
        }
    }

    /**
     * Set an key-value array of options to be JSON-encoded.
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = json_encode($options);
    }

    /**
     * Get the attachments for this block.
     *
     * @return ExhibitBlockAttachment[]
     */
    public function getAttachments()
    {
        return $this->getTable('ExhibitBlockAttachment')->findByBlock($this);
    }

    /**
     * Set attachment data for this block by array.
     *
     * @param array $attachmentsData Array of key-value arrays of data for each
     *  attachment.
     * @param boolean $deleteExtras Whether to delete extra preexisting
     *  attachments after setting new data.
     */
    public function setAttachments($attachmentsData, $deleteExtras = true)
    {
        // We have to have an ID to proceed.
        if (!$this->exists()) {
            $this->save();
        }

        $existingAttachments = $this->getAttachments();
        foreach ($attachmentsData as $i => $attachmentData) {
            if (!empty($existingAttachments)) {
                $attachment = array_pop($existingAttachments);
            } else {
                $attachment = new ExhibitBlockAttachment;
                $attachment->block_id = $this->id;
            }
            $attachment->setData($attachmentData);
            $attachment->save();
        }

        if ($deleteExtras) {
            foreach ($existingAttachments as $extraAttachment) {
                $extraAttachment->delete();
            }
        }
    }

    /**
     * Get the layout object for this page's layout.
     *
     * @return ExhibitLayout
     */
    public function getLayout()
    {
        return ExhibitLayout::getLayout($this->layout);
    }

    /**
     * Get the stem for form name attributes for this block. The stem uses
     * integer keys, based on the initial order of the block.
     *
     * @return string
     */
    public function getFormStem()
    {
        return 'blocks[' . ($this->order - 1) . ']';
    }
}
