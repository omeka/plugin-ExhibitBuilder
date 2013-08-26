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
    private $_parentFields;

    function __construct($record, $options = array())
    {
        parent::__construct($record);

        $defaultOptions = array(
            'parentFields' => array(),
            'slugMaxLength' => 30,
            'slugSeedFieldName' => 'title');

        $errorMsgs = array(
            'slugEmptyErrorMessage' => 'Slug must be provided.',
            'slugLengthErrorMessage' => 'Slug must not be more than ' . $defaultOptions['slugMaxLength'] . ' characters.',
            'slugUniqueErrorMessage' => 'Slug must be unique.');

        // Options passed in will override the defaults.
        $this->options = array_merge($defaultOptions, $errorMsgs, $options);

        $this->_parentFields = $this->options['parentFields'];

        $this->_record = $record;
    }

    private function _filterByParents($select)
    {
        if ($this->_parentFields) {
            foreach ($this->_parentFields as $field) {
                $parentId = $this->_record->{$field};
                if($parentId) {
                    $select->where($field . ' = ?', $parentId);
                } else {
                    $select->where($field . ' IS NULL');
                }
            }
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

        $table = $this->_record->getTable();
        $tableAlias = $table->getTableAlias();
        $select = $table->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)->from(array(), "COUNT(DISTINCT($tableAlias.id))");
        $select->where("$tableAlias.slug = ?", $slug);

        $this->_filterByParents($select);

        //If the record is persistent, get the count of pages
        //with that slug that aren't this particular record
        if($this->_record->exists()) {
            $select->where("$tableAlias.id != ?", $this->_record->id);
        }
        //If there are no other pages with that particular slug, then it is unique
        $count = (int) $db->fetchOne($select);
        return ($count == 0);
    }
}
