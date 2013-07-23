<?php

/**
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitAttachment extends Zend_View_Helper_Abstract
{
    /**
     * Return the form for making attachments to an Exhibit block.
     * 
     * @return string
     */
    public function exhibitAttachment($attachment, $fileOptions = array(), $linkProps = array())
    {
        $item = $attachment->getItem();
        $file = $attachment->getFile();

        if (!isset($fileOptions['linkAttributes']['href'])) {
            $fileOptions['linkAttributes']['href'] = exhibit_builder_exhibit_item_uri($item);
        }

        if (!isset($fileOptions['imgAttributes']['alt'])) {
            $fileOptions['imgAttributes']['alt'] = metadata($item, array('Dublin Core', 'Title'));
        }
    
        if ($file) {
            $html = file_markup($file, $fileOptions, null);
        } else if($item) {
            $html = exhibit_builder_link_to_exhibit_item(null, $linkProps, $item);
        }

        $html .= $this->_caption($attachment);

        return apply_filters('exhibit_builder_attachment_markup', $html,
            compact('attachment', 'fileOptions', 'linkProperties')
        );
    }

    protected function _caption($attachment)
    {
        if (!is_string($attachment['caption']) || $attachment['caption'] == '') {
            return '';
        }

        $html = '<div class="exhibit-item-caption">'
              . $attachment['caption']
              . '</div>';

        return apply_filters('exhibit_builder_caption', $html, array(
            'attachment' => $attachment
        ));
    }
}
