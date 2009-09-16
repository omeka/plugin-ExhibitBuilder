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
                        
                        case 'recent':
                        default:
                            $select->order("e.id DESC");
                        
                            //$select->order("added DESC");
                            break;                            
                    }
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
    public function count($params)
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
}
 
?>
