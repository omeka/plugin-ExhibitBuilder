<?php

/**
 * View helper for block attachments form.
 * 
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitFormAttachments extends Zend_View_Helper_Abstract
{
    /**
     * Return the form for making attachments to an Exhibit block.
     *
     * @param ExhibitPageBlock $block Block to make form for
     * @return string
     */
    public function exhibitFormAttachments($block)
    {
        $attachments = $block->ExhibitBlockAttachment;

        $html = '<div class="selected-item-list">';

        foreach ($attachments as $index => $attachment) {
            $html .= $this->view->partial('exhibits/attachment.php',
                array(
                    'attachment' => $attachment,
                    'block' => $block,
                    'index' => $index
                )
            );
        }
        $html .= '<div class="add-item button">Add Item</div>';
        $html .= '</div>';
        return $html;
    }
}
