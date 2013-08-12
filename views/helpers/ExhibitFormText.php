<?php
/**
 *`View helper for block text form.
 * 
 * @package ExhibitBuilder\View\Helper
 */
class ExhibitBuilder_View_Helper_ExhibitFormText extends Zend_View_Helper_Abstract
{
    /**
     * Return the form for adding text to an Exhibit block.
     *
     * @param ExhibitPageBlock $block
     * @return string
     */
    public function exhibitFormText($block)
    {
        return $this->view->formTextarea($block->getFormStem() . '[text]',
            $block->text, array('rows' => 8));
    }
}
