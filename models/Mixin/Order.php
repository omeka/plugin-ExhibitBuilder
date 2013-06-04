<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * Order mixin.
 * 
 * @package ExhibitBuilder
 * @subpackage Mixins
 */
class Mixin_Order extends Omeka_Record_Mixin_AbstractMixin
{
    public function __construct($record, $childClass, $childFk)
    {
        parent::__construct($record);
        $this->childClass = $childClass;
        $this->childFk = $childFk;
    }

    public function loadOrderedChildren()
    {
        $id = (int) $this->_record->id;
        $db = $this->_record->getDb();
        $target = $this->childClass;

        $sql = "
        SELECT s.*
        FROM {$db->$target} s
        WHERE s.{$this->childFk} = $id
        ORDER BY s.`order` ASC";

        return $db->getTable($target)->fetchObjects($sql);
    }

    public function addChild(Omeka_Record $child)
    {
        if (!$this->_record->exists()) {
            throw new Omeka_Record_Exception(__('Cannot add a child to a record that does not exist yet!'));
        }

        if (!($child instanceof $this->childClass)) {
            throw new Omeka_Record_Exception(__('Child must be an instance of "%s"', $this->childClass));
        }

        $fk = $this->childFk;

        $child->$fk = $this->_record->id;

        $new_order = $this->getChildCount() + 1;

        $child->order = $new_order;

        return $child;
    }

    public function getChildCount()
    {
        $db = $this->getDb();

        $target = $this->childClass;

        $sql = "
        SELECT COUNT(*)
        FROM {$db->$target}
        WHERE $this->childFk = ?";
        return $db->fetchOne($sql, array($this->_record->id));
    }
}
