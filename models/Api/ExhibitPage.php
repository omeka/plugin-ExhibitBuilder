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
        
        $pageBlocks = $record->getPageBlocks($record);
        
        $representation['page_blocks'] = array();
        
        foreach($pageBlocks as $pageBlock) {
            $blockRepresentation = array(
                'id'          => $pageBlock->id,
                'page_id'     => $pageBlock->page_id,
                'layout'      => $pageBlock->layout,
                'options'     => json_decode($pageBlock->options, true),
                'text'        => $pageBlock->text,
                'order'       => $pageBlock->order,
                'attachments' => array()
                );

            $blockAttachments = $pageBlock->getAttachments();

            foreach($blockAttachments as $attachment) {
                
                $attachmentRepresentation = 
                    array(
                        'id'   => $attachment->id,
                        'caption' => $attachment->caption,
                        'item' => array(
                            'id'       => $attachment->item_id,
                            'resource' => 'items',
                            'url'      => self::getResourceUrl("/items/{$attachment->item_id}")
                            ),

                        );
                if($attachment->file_id) {
                    $attachmentRepresentation['file'] = array(
                        'id'       => $attachment->file_id,
                        'resource' => 'files',
                        'url'      => self::getResourceUrl("/files/{$attachment->file_id}")
                        );                  
                }
                $blockRepresentation['attachments'][] = $attachmentRepresentation;
            }
            $representation['page_blocks'][] = $blockRepresentation; 
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
    
    public function getResourceId()
    {
        // This is typically the name of the plugin, an underscore, and the pluralized record type.
        return 'ExhibitBuilder_ExhibitPages';
    }    
}    
