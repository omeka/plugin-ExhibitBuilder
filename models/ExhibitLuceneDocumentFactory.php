<?php
/**
 * Lucene document factory class for exhibits, exhibit sections, and exhibit pages.
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-20009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

class ExhibitLuceneDocumentFactory
{
    static private $_instance;
    
    /**
     * Gets the single instance of ExhibitLuceneDocumentFactory
     *
     * @return ExhibitLuceneDocumentFactory
     **/
    public static function getInstance()
    {
        if (!self::$_instance) {
            try {
                self::$_instance = new self();
            } catch (Exception $e) {
                return null;
            }
        }
        return self::$_instance;
    }

	public function createDocument($record)
	{
		$doc = null;
		$recordClass = get_class($record);
	    switch($recordClass)
	    {
	        case 'Exhibit':
	            $doc = $this->createDocumentForExhibit($record);
	        break;
	        case 'ExhibitSection':
	            $doc = $this->createDocumentForExhibitSection($record);
	        break;
	        case 'ExhibitPage':
	            $doc = $this->createDocumentForExhibitPage($record);
	        break;
	    }
	    return $doc;
	}
	
	/**
     * Creates and returns a Zend_Search_Lucene_Document for the Exhibit
     *
     * @return Zend_Search_Lucene_Document
     **/
    public function createDocumentForExhibit($exhibit) 
    {   
        $doc = null;
        if ($search = LuceneSearch_Search::getInstance()) {
            
			$doc = new Zend_Search_Lucene_Document(); 
            
            // adds the fields for public and private       
            $search->addLuceneField($doc, 'Keyword', LuceneSearch_Search::FIELD_NAME_IS_PUBLIC, $exhibit->public == '1' ? LuceneSearch_Search::FIELD_VALUE_TRUE : LuceneSearch_Search::FIELD_VALUE_FALSE, true);         
            
            // adds the fields for public and private       
            $search->addLuceneField($doc, 'Keyword', LuceneSearch_Search::FIELD_NAME_IS_FEATURED, $exhibit->featured == '1' ? LuceneSearch_Search::FIELD_VALUE_TRUE : LuceneSearch_Search::FIELD_VALUE_FALSE, true);   

            // Adds fields for title, description, and slug
            $search->addLuceneField($doc, 'UnStored', array('Exhibit', 'title'), $exhibit->title);
            $contentFieldValue .= $exhibit->title . "\n";

            $search->addLuceneField($doc, 'UnStored', array('Exhibit', 'description'), $exhibit->description);
            $contentFieldValue .= $exhibit->description . "\n";

            $search->addLuceneField($doc, 'UnStored', array('Exhibit', 'slug'), $exhibit->slug);
            $contentFieldValue .= $exhibit->slug . "\n";

            //add the tags under the 'tag' field
            $tags = $exhibit->getTags();
            $tagNames = array();
            foreach($tags as $tag) {
                $tagNames[] = $tag->name;
            }
            if (count($tagNames) > 0) {
                $search->addLuceneField($doc, 'UnStored', LuceneSearch_Search::FIELD_NAME_TAG, $tagNames);
                $contentFieldValue .= implode(' ', $tagNames) . "\n";            
            }

	        if (trim($contentFieldValue) != '') {
	            $search->addLuceneField($doc, 'UnStored', LuceneSearch_Search::FIELD_NAME_CONTENT, $contentFieldValue);                
	        }
        }
        return $doc;
    }
	
	/**
     * Creates and returns a Zend_Search_Lucene_Document for the ExhibitSection
     *
     * @return Zend_Search_Lucene_Document
     **/
    public function createDocumentForExhibitSection($exhibitSection) 
    {   
		$doc = null;
        if ($search = LuceneSearch_Search::getInstance()) {
            
			$doc = new Zend_Search_Lucene_Document(); 
            
            // adds the fields for public and private       
            $isPublic = $exhibitSection->getExhibit()->public;
            $search->addLuceneField($doc, 'Keyword', LuceneSearch_Search::FIELD_NAME_IS_PUBLIC, $isPublic == '1' ? LuceneSearch_Search::FIELD_VALUE_TRUE : LuceneSearch_Search::FIELD_VALUE_FALSE, true);

            // Adds fields for title, description, and slug
            $search->addLuceneField($doc, 'UnStored', array('ExhibitSection', 'title'), $exhibitSection->title);
            $contentFieldValue .= $exhibitSection->title . "\n";
            
            $search->addLuceneField($doc, 'UnStored', array('ExhibitSection', 'description'), $exhibitSection->description);
            $contentFieldValue .= $exhibitSection->description . "\n";
            
            $search->addLuceneField($doc, 'UnStored', array('ExhibitSection', 'slug'), $exhibitSection->slug);
            $contentFieldValue .= $exhibitSection->slug . "\n";

            // add the exhibit id of the the exhibit that contains the section.
            if ($exhibitSection->exhibit_id) {
                $search->addLuceneField($doc, 'Keyword', array('ExhibitSection','exhibit_id'), $exhibitSection->getExhibit()->id, true);                        
            }

	        if (trim($contentFieldValue) != '') {
	            $search->addLuceneField($doc, 'UnStored', LuceneSearch_Search::FIELD_NAME_CONTENT, $contentFieldValue);                
	        }
        }
        return $doc;
    }
	
	/**
     * Creates and returns a Zend_Search_Lucene_Document for the ExhibitPage
     *
     * @return Zend_Search_Lucene_Document
     **/
    public function createDocumentForExhibitPage($exhibitPage)
    {   
		$doc = null;
        if ($search = LuceneSearch_Search::getInstance()) {
        
            $doc = new Zend_Search_Lucene_Document(); 

            // Add the fields for public and private       
            $isPublic = $exhibitPage->getSection()->getExhibit()->public;
            $search->addLuceneField($doc, 'Keyword', LuceneSearch_Search::FIELD_NAME_IS_PUBLIC, $isPublic == '1' ? LuceneSearch_Search::FIELD_VALUE_TRUE : LuceneSearch_Search::FIELD_VALUE_FALSE, true);

            // Add fields for title and text
            $search->addLuceneField($doc, 'UnStored', array('ExhibitPage', 'title'), $exhibitPage->title);
            $contentFieldValue .= $exhibitPage->title . "\n";

            // Add the section id of the section that contains the page
            if ($exhibitPage->section_id) {
                $search->addLuceneField($doc, 'Keyword', array('ExhibitPage','section_id'), $exhibitPage->section_id, true);                        
            }
            
            // add the exhibit id of the exhibit that contains the page
            if ($exhibitPage->section_id) {
                $search->addLuceneField($doc, 'Keyword', array('ExhibitPage','exhibit_id'), $exhibitPage->getSection()->getExhibit()->id, true);                        
            }

            // Add field for page entry texts.
            $entries = $exhibitPage->getPageEntries();
            $entryTexts = array();
            foreach ($entries as $entry) {
                $entryTexts[] = $entry->text;
            }
            if(count($entryTexts) > 0) {
                $search->addLuceneField($doc, 'UnStored', array('ExhibitPage', 'entry_texts'), $entryTexts);    
                $contentFieldValue .= implode(' ', $entryTexts) . "\n";            
            }
    
			if (trim($contentFieldValue) != '') {
	            $search->addLuceneField($doc, 'UnStored', LuceneSearch_Search::FIELD_NAME_CONTENT, $contentFieldValue);                
	        }
        }
        return $doc;
    }
}