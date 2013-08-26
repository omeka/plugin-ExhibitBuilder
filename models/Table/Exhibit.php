<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * Exhibit table class.
 *
 * @package ExhibitBuilder
 */
class Table_Exhibit extends Omeka_Db_Table
{
    public function getSelect()
    {
        $select = parent::getSelect();
        $permissions = new Omeka_Db_Select_PublicPermissions('ExhibitBuilder_Exhibits');
        $permissions->apply($select, 'exhibits');
        return $select;
    }

    public function applySearchFilters($select, $params)
    {
        $db = $this->getDb();

        foreach($params as $paramName => $paramValue) {
            switch($paramName) {
                case 'tag':
                case 'tags':
                    $tags = explode(',', $paramValue);
                    $select->joinInner(array('tg'=>$db->RecordsTags), 'tg.record_id = exhibits.id', array());
                    $select->joinInner(array('t'=>$db->Tag), "t.id = tg.tag_id", array());
                    foreach ($tags as $k => $tag) {
                        $select->where('t.name = ?', trim($tag));
                    }
                    $select->where("tg.record_type = ? ", array('Exhibit'));
                    break;
                case 'limit':
                    $select->limit($paramValue);
                    break;

                case 'sort':
                    switch($paramValue) {
                        case 'alpha':
                            $select->order("exhibits.title ASC");
                            break;

                        case 'recent':
                            $select->order("exhibits.id DESC");
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
        return $select;
    }

    public function findBySlug($slug)
    {
        $select = $this->getSelect();
        $select->where("exhibits.slug = ?");
        $select->limit(1);
        return $this->fetchObject($select, array($slug));
    }

    public function exhibitHasItem($exhibit_id, $item_id)
    {
        $db = $this->getDb();

        $sql = "SELECT COUNT(i.id) FROM $db->Item i
                INNER JOIN $db->ExhibitPageEntry ip ON ip.item_id = i.id
                INNER JOIN $db->ExhibitPage sp ON sp.id = ip.page_id
                INNER JOIN $db->Exhibit e ON e.id = sp.exhibit_id
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
        $select = $this->getSelect();
        $select->where("exhibits.featured = 1")->order("RAND()")->limit(1);

        return $this->fetchObject($select);
    }

    protected function _getColumnPairs()
    {
        return array('exhibits.id', 'exhibits.title');
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
            $select->where('exhibits.public = 1');
        } else {
            $select->where('exhibits.public = 0');
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
            $select->where('exhibits.featured = 1');
        } else {
            $select->where('exhibits.featured = 0');
        }
    }
}
