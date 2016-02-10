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
    /**
     * Use SQL-based low-level permissions checking for exhibit queries.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();
        $permissions = new Omeka_Db_Select_PublicPermissions('ExhibitBuilder_Exhibits');
        $permissions->apply($select, 'exhibits');
        return $select;
    }

    /**
     * Define filters for browse and findBy.
     *
     * Available filters are: "tag" or "tags", "public" and "featured". "sort"
     * also adds specific sorting strategies "alpha" and "recent", but the
     * normal sorting can also be used.
     *
     * @param Omeka_Db_Select $select
     * @param array $params Key-value array of search parameters
     * @return Omeka_Db_Select
     */
    public function applySearchFilters($select, $params)
    {
        $db = $this->getDb();
        parent::applySearchFilters($select, $params);

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
                case 'public':
                    $this->filterByPublic($select, $params['public']);
                    break;
                case 'range':
                    $this->filterByRange($select, $params['range']);
                    break;
                case 'featured':
                    $this->filterByFeatured($select, $params['featured']);
                    break;
            }
        }
        return $select;
    }

    /**
     * Find an exhibit by its slug.
     *
     * @param string $slug
     */
    public function findBySlug($slug)
    {
        $select = $this->getSelect();
        $select->where("exhibits.slug = ?");
        $select->limit(1);
        return $this->fetchObject($select, array($slug));
    }

    /**
     * Find whether an exhibit has a specific item.
     *
     * @param integer $exhibit_id The ID of the exhibit to check in
     * @param integer $item_id The ID of the item to check for
     * @return boolean
     */
    public function exhibitHasItem($exhibit_id, $item_id)
    {
        $db = $this->getDb();

        $sql = "SELECT COUNT(i.id) FROM $db->Item i
                INNER JOIN $db->ExhibitBlockAttachment eba ON eba.item_id = i.id
                INNER JOIN $db->ExhibitPageBlock epb ON epb.id = eba.block_id
                INNER JOIN $db->ExhibitPage ep ON ep.id = epb.page_id
                INNER JOIN $db->Exhibit e ON e.id = ep.exhibit_id
                WHERE e.id = ? AND i.id = ?";

        $count = (int) $db->fetchOne($sql, array((int) $exhibit_id, (int) $item_id));

        return ($count > 0);
    }

    /**
     * Get a random featured Exhibit.
     * 
     * @return Exhibit
     */
    public function findRandomFeatured()
    {
        $select = $this->getSelect();
        $select->where("exhibits.featured = 1")->order("RAND()")->limit(1);

        return $this->fetchObject($select);
    }

    /**
     * Get column names to be used for making a select dropdown.
     *
     * @return array
     */
    protected function _getColumnPairs()
    {
        return array('exhibits.id', 'exhibits.title');
    }
}
