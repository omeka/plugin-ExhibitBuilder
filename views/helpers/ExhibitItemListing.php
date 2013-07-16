<?php

/**
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitItemListing extends Zend_View_Helper_Abstract
{
    /**
     * Return the form for making attachments to an Exhibit.
     * 
     * @return string
     */
    public function exhibitItemListing($item)
    {
        $html = '<div class="item-listing" data-item-id="' . $item->id . '">'
              . '<h4 class="title">'
              . metadata($item, array('Dublin Core', 'Title'))
              . '</h4>';
        if (metadata($item, 'has files')) {
            foreach ($item->Files as $displayFile) {
                if ($displayFile->hasThumbnail()) {
                    $html .= '<div class="item-file">'
                        . file_image('square_thumbnail', array(), $displayFile)
                        . '</div>';
                }
            }
        }
        $html .= '</div>';
        return $html;
    }
}
