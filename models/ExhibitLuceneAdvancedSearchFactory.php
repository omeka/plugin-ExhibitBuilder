<?php
/**
 * Lucene advanced search factory class for exhibits, exhibit sections, and exhibit pages.
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-20009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/
 
class ExhibitLuceneAdvancedSearchFactory
{
    static private $_instance;
    
    /**
     * Gets the single instance of ExhibitLuceneAdvancedSearchFactory
     *
     * @return ExhibitLuceneAdvancedSearchFactory
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

    public function addAdvancedSearchQuery($modelName, $searchQuery, $requestParams) 
    {    
        switch($modelName) {
            case 'Exhibit':
                $this->addAdvancedSearchQueryForExhibit($searchQuery, $requestParams);
            break;
            case 'ExhibitSection':
                $this->addAdvancedSearchQueryForExhibitSection($searchQuery, $requestParams);
            break;
            case 'ExhibitPage':
                $this->addAdvancedSearchQueryForExhibitPage($searchQuery, $requestParams);
            break;
        }
    }

    /**
     * Adds an advanced search subquery to the lucene search query 
     *
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param string|array $requestParams An associative array of request parameters
     */
    public function addAdvancedSearchQueryForExhibit($searchQuery, $requestParams) 
    {
        if ($search = LuceneSearch_Search::getInstance()) {
        
            // Build an advanced search query for the item
            $advancedSearchQueryForExhibit = new Zend_Search_Lucene_Search_Query_Boolean();
            foreach($requestParams as $requestParamName => $requestParamValue) {
                switch($requestParamName) {

                    case 'public':
                        if (is_true($requestParamValue)) {
                            $subquery = $search->getLuceneTermQueryForFieldName(LuceneSearch_Search::FIELD_NAME_IS_PUBLIC, LuceneSearch_Search::FIELD_VALUE_TRUE);
                            $advancedSearchQueryForItem->addSubquery($subquery, true);
                        }
                    break;

                    case 'tag':
                    case 'tags':
                        $this->filterByTagsForExhibit($advancedSearchQueryForExhibit, $requestParamValue);
                        break;

                }
            }

            // add the exhibit advanced search query to the searchQuery as a disjunctive subquery 
            // (i.e. there will be OR statements between each of models' the advanced search queries)
            $searchQuery->addSubquery($advancedSearchQueryForExhibit);
        }        
    }

    /**
     * Filters the exhibit by comma-delimited tags
     * 
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param string|array $tags A comma-delimited string or an array of tag 
     *         names.
     */
    public function filterByTagsForExhibit($searchQuery, $tags)
    {
        if ($search = LuceneSearch_Search::getInstance()) {
            if (!is_array($tags)) {
                $tags = explode(',', $tags);
            }
            // make all of the tags required (i.e. conjoin the tags with AND)
            foreach ($tags as $tag) {
                $subquery = $search->getLuceneTermQueryForFieldName(LuceneSearch_Search::FIELD_NAME_TAG, trim($tag));
                $searchQuery->addSubquery($subquery, true);
            }
        }
    }
    
    
    /**
     * Adds an advanced search subquery for an exhibit section
     *
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param string|array $requestParams An associative array of request parameters
     */
    public function addAdvancedSearchQueryForExhibitSection($searchQuery, $requestParams) 
    {
        if ($search = LuceneSearch_Search::getInstance()) {

            // Build an advanced search query for the exhibit section
            $advancedSearchQueryForExhibitSection = new Zend_Search_Lucene_Search_Query_Boolean();
            foreach($requestParams as $requestParamName => $requestParamValue) {
                switch($requestParamName) {

                    case 'public':
                        if (is_true($requestParamValue)) {
                            $subquery = $search->getLuceneTermQueryForFieldName(LuceneSearch_Search::FIELD_NAME_IS_PUBLIC, LuceneSearch_Search::FIELD_VALUE_TRUE);
                            $advancedSearchQueryForExhibitSection->addSubquery($subquery, true);
                        }
                    break;

                    case 'exhibit_id':
                        $this->filterByExhibitForExhibitSection($advancedSearchQueryForExhibitSection, $requestParamValue);
                    break;

                }
            }

            // add the exhibit section advanced search query to the searchQuery as a disjunctive subquery 
            // (i.e. there will be OR statements between each of models' the advanced search queries)
            $searchQuery->addSubquery($advancedSearchQueryForExhibitSection);
        }        
    }
   
    /**
     * Filters the exhibit sections by exhibit
     *
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param integer|string $exhibitId The id the exhibit to filter by
     */
    public function filterByExhibitForExhibitSection($searchQuery, $exhibitId)
    {
        if ($search = LuceneSearch_Search::getInstance()) {
          $subquery = $search->getLuceneTermQueryForFieldName(array('ExhibitSection','exhibit_id'), $exhibitId);
          $searchQuery->addSubquery($subquery, true);
        }
    }
    
    /**
     * Adds an advanced search query for an exhibit page
     *
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param string|array $requestParams An associative array of request parameters
     */
    public function addAdvancedSearchQueryForExhibitPage($searchQuery, $requestParams) 
    {
        if ($search = LuceneSearch_Search::getInstance()) {

            // Build an advanced search query for the exhibit page
            $advancedSearchQueryForExhibitPage = new Zend_Search_Lucene_Search_Query_Boolean();
            foreach($requestParams as $requestParamName => $requestParamValue) {
                switch($requestParamName) {

                    case 'public':
                        if (is_true($requestParamValue)) {
                            $subquery = $search->getLuceneTermQueryForFieldName(LuceneSearch_Search::FIELD_NAME_IS_PUBLIC, LuceneSearch_Search::FIELD_VALUE_TRUE);
                            $advancedSearchQueryForExhibitPage->addSubquery($subquery, true);
                        }
                    break;

                    case 'exhibit_id':
                        $this->filterByExhibitForExhibitPage($advancedSearchQueryForExhibitPage, $requestParamValue);
                    break;

                    case 'section_id':
                        $this->filterByExhibitSectionForExhibitPage($advancedSearchQueryForExhibitPage, $requestParamValue);
                    break;
                }
            }

            // add the exhibit page advanced search query to the searchQuery as a disjunctive subquery 
            // (i.e. there will be OR statements between each of models' the advanced search queries)
            $searchQuery->addSubquery($advancedSearchQueryForExhibitPage);
        }        
    }
   
    /**
     * Filters the exhibit pages by exhibit
     *
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param integer|string $exhibitId The id the exhibit to filter by
     */
    public function filterByExhibitForExhibitPage($searchQuery, $exhibitId)
    {
        if ($search = LuceneSearch_Search::getInstance()) {
          $subquery = $search->getLuceneTermQueryForFieldName(array('ExhibitPage','exhibit_id'), $exhibitId);
          $searchQuery->addSubquery($subquery, true);
        }
    }
    
    /**
     * Filters the exhibit pages by exhibit section
     *
     * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
     * @param integer|string $exhibitId The id the exhibit to filter by
     */
    public function filterByExhibitSectionForExhibitPage($searchQuery, $sectionId)
    {
        if ($search = LuceneSearch_Search::getInstance()) {
          $subquery = $search->getLuceneTermQueryForFieldName(array('ExhibitPage','section_id'), $sectionId);
          $searchQuery->addSubquery($subquery, true);
        }
    }
}