<?php 
/**
 * Exhibit table class
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

class ExhibitTable extends Omeka_Db_Table
{
    
    public function applySearchFilters($select, $params)
    {        
        $db = $this->getDb();
        
        foreach($params as $paramName => $paramValue) {
            switch($paramName) {
                case 'tag':
                case 'tags':
                    $tags = explode(',', $paramValue);
                    $select->joinInner(array('tg'=>$db->Taggings), 'tg.relation_id = e.id', array());
                    $select->joinInner(array('t'=>$db->Tag), "t.id = tg.tag_id", array());
                    foreach ($tags as $k => $tag) {
                        $select->where('t.name = ?', trim($tag));
                    }
                    $select->where("tg.type = ? ", array('Exhibit'));
                    break;
                case 'limit':
                    $select->limit($paramValue);
                    break;
                
                case 'sort':
                    switch($paramValue) {   
                        case 'alpha':
                            $select->order("e.title ASC");
                            break;
                        
                        case 'recent':
                            $select->order("e.id DESC");
                            break;     
                    }
                    break;
                case 'public':
                    $this->filterByPublic($select, $params['public']);
                    break;
                case 'featured':
                    $this->filterByFeatured($select, $params['featured']);
                    break;
            }
        }
        
        new ExhibitPermissions($select);
                
        return $select;
    }
    
    public function findBySlug($slug)
    {
        $db = $this->getDb();
        $select = new Omeka_Db_Select;
        $select->from(array('e'=>$db->Exhibit), array('e.*'));
        $select->where("e.slug = ?");
        $select->limit(1);
        new ExhibitPermissions($select);
        return $this->fetchObject($select, array($slug));       
    }
    
    /**
     * Override Omeka_Table::count() to retrieve a permissions-limited
     *
     * @return void
     **/
    public function count($params = array())
    {
        $db = $this->getDb();
        
        $select = new Omeka_Db_Select;
        $select->from(array('e'=>$db->Exhibit), "COUNT(DISTINCT(e.id))");
        new ExhibitPermissions($select);
        
        $this->applySearchFilters($select, $params);
           
        return $db->fetchOne($select);
    }
    
    public function find($id)
    {
        $db = $this->getDb();
        
        $select = new Omeka_Db_Select;
        $select->from(array('e'=>$db->Exhibit), array('e.*'));
        $select->where("e.id = ?");
        
        new ExhibitPermissions($select);
        
        return $this->fetchObject($select, array($id));
    }
    
    public function exhibitHasItem($exhibit_id, $item_id)
    {
        $db = $this->getDb();
        
        $sql = "SELECT COUNT(i.id) FROM $db->Item i 
                INNER JOIN $db->ExhibitPageEntry ip ON ip.item_id = i.id 
                INNER JOIN $db->ExhibitPage sp ON sp.id = ip.page_id
                INNER JOIN $db->ExhibitSection s ON s.id = sp.section_id
                INNER JOIN $db->Exhibit e ON e.id = s.exhibit_id
                WHERE e.id = ? AND i.id = ?";
                
        $count = (int) $db->fetchOne($sql, array((int) $exhibit_id, (int) $item_id));

        return ($count > 0);
    }
        
    /**
     * @duplication CollectionTable::findRandomFeatured(), ItemTable::findRandomFeatured()
     *
     * @return Exhibit|false
     **/
    public function findRandomFeatured()
    {
        $db = $this->getDb();
        
        $select = new Omeka_Db_Select;
        $select->from(array('e'=>$db->Exhibit))->where("e.featured = 1")->order("RAND()")->limit(1);
        
        return $this->fetchObject($select);
    }
    
    protected function _getColumnPairs()
    {        
        return array('e.id', 'e.title');
    }
    
    /**
     * Apply a filter to the exhibits based on whether or not they are public
     * 
     * @param Zend_Db_Select
     * @param boolean Whether or not to retrieve only public exhibits
     * @return void
     **/
    public function filterByPublic($select, $isPublic)
    {         
        $isPublic = (bool) $isPublic; // this makes sure that empty strings and unset parameters are false

        //Force a preview of the public collections
        if ($isPublic) {
            $select->where('e.public = 1');
        } else {
            $select->where('e.public = 0');
        }
    }
    
    /**
     * Apply a filter to the exhibits based on whether or not they are featured
     * 
     * @param Zend_Db_Select
     * @param boolean Whether or not to retrieve only public exhibits
     * @return void
     **/
    public function filterByFeatured($select, $isFeatured)
    {
        $isFeatured = (bool) $isFeatured; // this make sure that empty strings and unset parameters are false
        
        //filter items based on featured (only value of 'true' will return featured collections)
        if ($isFeatured) {
            $select->where('e.featured = 1');
        } else {
            $select->where('e.featured = 0');
        }     
    }
}