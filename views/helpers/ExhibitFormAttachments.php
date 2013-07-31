<?php

/**
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitFormAttachments extends Zend_View_Helper_Abstract
{
    /**
     * Return the form for making attachments to an Exhibit block.
     * 
     * @return string
     */
    public function exhibitFormAttachments($block, $maxAttachments = 0)
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

        $html .= '</div>';
        $html .= '<div class="add-item button"><a href="#">Add item</a></div>';
        return $html;
    }
}
