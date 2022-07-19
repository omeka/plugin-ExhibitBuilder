<?php

/**
 * Exhibit carousel view helper.
 * 
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitAttachmentCarousel extends Zend_View_Helper_Abstract
{
    /**
     * Return the markup for a carousel of exhibit attachments.
     *
     * @uses ExhibitBuilder_View_Helper_ExhibitAttachment
     * @param ExhibitBlockAttachment[] $attachments
     * @param array $configs
     * @param array $linkProps
     * @return string
     */
    public function exhibitAttachmentCarousel($attachments, $configs = array(), $linkProps = array())
    {        
        $html = '<div class="jcarousel">';
        $html .= '<ul>';
        foreach  ($attachments as $attachment) {
            $item = $attachment->getItem();
            $html .= '<li>';
            $html .= $this->view->exhibitAttachment($attachment, array('imageSize' => $configs['file-size']), $linkProps, true);
            if(isset($configs['show-title']) && $configs['show-title']) {
                $html .= '<p class="slide-title">' . exhibit_builder_link_to_exhibit_item(null, $linkProps, $item) . '</p>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<a href="#" class="jcarousel-control-prev">&lsaquo;</a>';
        $html .= '<a href="#" class="jcarousel-control-next">&rsaquo;</a>';
        $html .= '<p class="jcarousel-pagination"></p>';
    
        return $html;
    }
}
