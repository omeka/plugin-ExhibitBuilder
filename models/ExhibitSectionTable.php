<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/
 
/**
 * 
 *
 * @package Omeka
 * @author CHNM
 * @copyright Center for History and New Media, 2007-2008
 **/
class ExhibitSectionTable extends Omeka_Db_Table
{
    protected $_name = 'sections';
    
    public function findPrevious($section)
       {
           return $this->findNearby($section, 'previous');
       }

       public function findNext($section)
       {
           return $this->findNearby($section, 'next');
       }

       protected function findNearby($section, $position = 'next')
       {
           $select = $this->getSelect();
           $select->limit(1);

           $exhibit = Zend_Registry::get('exhibit');

           switch ($position) {
               case 'next':
                   $select->where('e.exhibit_id = ?', (int) $exhibit->id);
                   $select->where('e.order > ?', (int) $section->order);
                   $select->order('e.order ASC');
                   break;

               case 'previous':
                   $select->where('e.exhibit_id = ?', (int) $exhibit->id);
                   $select->where('e.order < ?', (int) $section->order);
                   $select->order('e.order DESC');
                   break;

               default:
                   throw new Exception( 'Invalid position provided to ExhibitPageTable::findNearby()!' );
                   break;
           }

           return $this->fetchObject($select);
      }
      
      /**
       * Adds an advanced search subquery to the lucene search query 
       *
       * @param Zend_Search_Lucene_Search_Query_Boolean $advancedSearchQuery
       * @param string|array $requestParams An associative array of request parameters
       */
      public function addAdvancedSearchQueryForLucene($advancedSearchQuery, $requestParams) 
      {
          if ($search = Omeka_Search::getInstance()) {

              // Build an advanced search query for the item
              $advancedSearchQueryForExhibitSection = new Zend_Search_Lucene_Search_Query_Boolean();
              foreach($requestParams as $requestParamName => $requestParamValue) {
                  switch($requestParamName) {

                      case 'public':
                          if (is_true($requestParamValue)) {
                              $subquery = $search->getLuceneTermQueryForFieldName(Omeka_Search::FIELD_NAME_IS_PUBLIC, Omeka_Search::FIELD_VALUE_TRUE, true);
                              $advancedSearchQueryForItem->addSubquery($subquery, true);
                          }
                      break;

                      case 'exhibit_id':
                          $this->filterByExhibitLucene($advancedSearchQueryForExhibit, $requestParamValue);
                          break;

                  }
              }

              // add the exhibit section advanced search query to the searchQuery as a disjunctive subquery 
              // (i.e. there will be OR statements between each of models' the advanced search queries)
              $advancedSearchQuery->addSubquery($advancedSearchQueryForExhibitSection);
          }        
      }
     
      /**
       * Filters the exhibit sections by exhibit
       *
       * @param Zend_Search_Lucene_Search_Query_Boolean $searchQuery
       * @param integer|string $exhibitId The id the exhibit to filter by
       */
      public function filterByExhibitLucene($searchQuery, $exhibitId)
      {
          if ($search = Omeka_Search::getInstance()) {
            $subquery = $search->getLuceneTermQueryForFieldName(array('ExhibitSection','exhibit_id'), $exhibitId, true);
            $searchQuery->addSubquery($subquery, true);
          }
      }
}