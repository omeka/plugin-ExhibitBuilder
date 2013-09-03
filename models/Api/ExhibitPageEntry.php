<?php

class Api_ExhibitPageEntry extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $db = get_db();
        $representation = array(
                'id' => $record->id,
                'text' => $record->text,
                'url' => self::getResourceUrl("/exhibit_page_entries/{$record->id}"),
                'caption' => $record->caption,
                'order' => $record->order
                );
        
        $representation['page'] = array(
                'id' => $record->page_id,
                'resource' => 'exhibit_pages',
                'url' => self::getResourceUrl("/exhibit_pages/{$record->page_id}")
                );

        //check if item is visible to user
        $item = $db->getTable('Item')->find($record->item_id);
        
        if($record->item_id && is_allowed($item, 'show')) {
            $representation['item'] = array(
                    'id' => $record->item_id,
                    'resource' => 'items',
                    'url' => self::getResourceUrl("/items/{$record->item_id}")
                    );
        } else {
            $representation['item'] = null;
        }
         
        //check if file is visible
        $file = $db->getTable('File')->find($record->file_id);
        if($record->file_id && is_allowed($file, 'show')) {
            $representation['file'] = array(
                    'id' => $record->file_id,
                    'resource' => 'files',
                    'url' => self::getResourceUrl("/files/{$record->file_id}")
                    );
        } else {
            $representation['file'] = null;
        }        
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
}