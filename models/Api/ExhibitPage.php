<?php

class Api_ExhibitPage extends Omeka_Record_Api_AbstractRecordAdapter
{
    
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
                'id' => $record->id,
                'url' => self::getResourceUrl("/exhibit_pages/{$record->id}"),
                'title' => $record->title,
                'slug' => $record->slug,
                'layout' => $record->layout,
                'order' => $record->order
                );
        
        $representation['exhibit'] = array(
                'id' => $record->exhibit_id,
                'resource' => 'exhibits',
                'url' => self::getResourceUrl("/exhibits/{$record->exhibit_id}")
                );
        if($record->parent_id) {
            $representation['parent'] = array(
                    'id' => $record->parent_id,
                    'resource' => 'exhibit_pages',
                    'url' => self::getResourceUrl("/exhibit_pages/{$record->parent_id}")
                    );            
        } else {
            $representation['parent'] = null;
        }
        
        $entriesCount = get_db()->getTable('ExhibitPageEntry')->count(array('page_id' => $record->id));
        $representation['exhibit_page_entries'] = array(
                'count' => $entriesCount,
                'resource' => 'exhibit_page_entries',
                'url' => self::getResourceUrl("/exhibit_page_entries?page_id={$record->id}")
                );
        
        return $representation;
    }
    
    // Set data to a record during a POST request.
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {
        // Set properties directly to a new record.
    }
    
    // Set data to a record during a PUT request.
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {
        // Set properties directly to an existing record.
    }
    
    public function getResourceId()
    {
        // This is typically the name of the plugin, an underscore, and the pluralized record type.
        return 'ExhibitBuilder_ExhibitPages';
    }    
}    
