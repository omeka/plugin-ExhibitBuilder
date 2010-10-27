<?php
/**
 * The only requirement for a record to use this mixin is that it needs a 
 * field named 'slug'.
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

class Sluggable extends Omeka_Record_Mixin
{
    function __construct($record, $options = array())
    {
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
        
        $this->record = $record;
    }
    
    private function getParentId()
    {
        if ($this->parentIdFieldName) {
            return $this->record->{$this->parentIdFieldName};
        }
    }
    
    public function beforeValidate()
    {
        $seedValue = '';

        // Create a slug if one was not specified.
        if (trim($this->record->slug) == '') {
            $seedValue = $this->record->{$this->options['slugSeedFieldName']};
        } else {
            $seedValue = $this->record->slug;
        }
        $this->record->slug = generate_slug($seedValue);
    }
    
    public function afterValidate()
    {
        if(trim($this->record->slug) == '') {
            $this->addError('slug', $this->options['slugEmptyErrorMessage']);
        }

        if(!$this->slugIsUnique($this->record->slug)) {
            $this->addError('slug', $this->options['slugUniqueErrorMessage']);
        } 
                
        if(strlen($this->record->slug) > $this->options['slugMaxLength']) {
            $this->addError('slug', $this->options['slugLengthErrorMessage']);
        }
    }
    
    public function slugIsUnique($slug)
    {
        $db = $this->getDb();
        
        $select = $this->record->getTable()->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)->from(array(), 'COUNT(DISTINCT(id))');
        $select->where('slug = ?', $slug);
                
        if ($this->parentIdFieldName) {
            $parentId = $this->getParentId();
          
            if(!$parentId) {
                throw new Exception('Cannot check for unique slugs if the record is not assigned a valid parent ID!');
            }
            
            $select->where($this->parentIdFieldName . ' = ?', $parentId);
        }
        
        //If the record is persistent, get the count of sections 
        //with that slug that aren't this particular record
        if($this->exists()) {
            $select->where('id != ?', $this->record->id);
        }
                        
        //If there are no other sections with that particular slug, then it is unique
        $count = (int) $db->fetchOne($select);
        return ($count == 0);       
    }
}