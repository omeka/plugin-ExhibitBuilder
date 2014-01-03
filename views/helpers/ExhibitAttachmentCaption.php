<?php

/**
 * Exhibit attachment caption view helper.
 * 
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitAttachmentCaption extends Zend_View_Helper_Abstract
{
    /**
     * Return the markup for an exhibit attachment's caption.
     *
     * @param ExhibitBlockAttachment $attachment
     * @return string
     */
    public function exhibitAttachmentCaption($attachment)
    {
        if (!is_string($attachment['caption']) || $attachment['caption'] == '') {
            return '';
        }

        $html = '<div class="exhibit-item-caption">'
              . $attachment['caption']
              . '</div>';

        return apply_filters('exhibit_attachment_caption', $html, array(
            'attachment' => $attachment
        ));
    }
}
