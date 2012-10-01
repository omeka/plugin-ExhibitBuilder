<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * Mixin for "sluggable" records.
 * 
 * The only requirement for a record to use this mixin is that it needs a
 * field named 'slug'.
 *
 * @package ExhibitBuilder
 */
class Mixin_Slug extends Omeka_Record_Mixin_AbstractMixin
{
    function __construct($record, $options = array())
    {
        parent::__construct($record);

        $defaultOptions = array(
            'parentIdFieldName'=>null,
            'slugMaxLength'=>30,
            'slugSeedFieldName'=>'title');

        $errorMsgs = array(
            'slugEmptyErrorMessage'=>'Slug must be provided.',
            'slugLengthErrorMessage'=>'Slug must not be more than ' . $defaultOptions['slugMaxLength'] . ' characters.',
            'slugUniqueErrorMessage'=>'Slug must be unique.');

        // Options passed in will override the defaults.
        $this->options = array_merge($defaultOptions, $errorMsgs, $options);

        $this->parentIdFieldName = $this->options['parentIdFieldName'];

        $this->_record = $record;
    }

    private function getParentId()
    {
        if ($this->parentIdFieldName) {
            return $this->_record->{$this->parentIdFieldName};
        }
    }

    public function beforeSave($args)
    {
        $seedValue = '';

        // Create a slug if one was not specified.
        if (trim($this->_record->slug) == '') {
            $seedValue = $this->_record->{$this->options['slugSeedFieldName']};
        } else {
            $seedValue = $this->_record->slug;
        }
        $this->_record->slug = exhibit_builder_generate_slug($seedValue);

        if(trim($this->_record->slug) == '') {
            $this->_record->addError('slug', $this->options['slugEmptyErrorMessage']);
        }

        if(!$this->slugIsUnique($this->_record->slug)) {
            $this->_record->addError('slug', $this->options['slugUniqueErrorMessage']);
        }

        if(strlen($this->_record->slug) > $this->options['slugMaxLength']) {
            $this->_record->addError('slug', $this->options['slugLengthErrorMessage']);
        }
    }

    public function slugIsUnique($slug)
    {
        $db = $this->_record->getDb();

        $select = $this->_record->getTable()->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)->from(array(), 'COUNT(DISTINCT(id))');
        $select->where('slug = ?', $slug);

        if ($this->parentIdFieldName) {
            $parentId = $this->getParentId();
            if($parentId) {
                $select->where($this->parentIdFieldName . ' = ?', $parentId);
            }

        }

        //If the record is persistent, get the count of pages
        //with that slug that aren't this particular record
        if($this->_record->exists()) {
            $select->where('id != ?', $this->_record->id);
        }

        //If there are no other pages with that particular slug, then it is unique
        $count = (int) $db->fetchOne($select);
        return ($count == 0);
    }
}
