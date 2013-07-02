<?php

class Api_ExhibitPageEntry extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
                'id' => $record->id,
                'text' => $record->text,
                'caption' => $record->caption,
                'order' => $record->order
                );
        
        $representation['page'] = array(
                'id' => $record->page_id,
                'url' => self::getResourceUrl("/exhibit_pages/{$record->page_id}")
                );
                
        if($record->item_id) {
            $representation['item'] = array(
                    'id' => $record->item_id,
                    'url' => self::getResourceUrl("/items/{$record->item_id}")
                    );
        } else {
            $representation['item'] = null;
        }
         
        if($record->file_id) {
            $representation['file'] = array(
                    'id' => $record->file_id,
                    'url' => self::getResourceUrl("/files/{$record->item_id}")
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