<?php
class ExhibitBuilder_ApiImport_ExhibitPageBlockAdapter extends ApiImport_ResponseAdapter_AbstractRecordAdapter
{
    protected $recordType = 'ExhibitPageBlock';

    public function import()
    {
        if(! $this->record) {
            $this->record = new ExhibitPageBlock;
        } 
        $this->record->page_id = $this->responseData['page_id'];
        $this->record->layout = $this->responseData['layout'];
        $this->record->text = $this->responseData['text'];
        $this->record->order = $this->responseData['order'];
        $this->record->options = json_encode($this->responseData['options']);
        try {
            $this->record->save(true);
            $this->addApiRecordIdMap();
        } catch(Exception $e) {
            _log($e);
        }

        $attachmentAdapter = new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $this->endpointUri, 'ExhibitBlockAttachment');
        $attachmentAdapter->setResourceProperties(array('file' => 'File', 
                                                        'item' => 'Item',
                                                        )
                                                  );
        foreach($this->responseData['attachments'] as $order=>$attachmentData) {
            $attachmentData['order'] = $order;
            $attachmentData['block_id'] = $this->record->id;
            $attachmentAdapter->resetResponseData($attachmentData);
            $attachmentAdapter->import();
        }
    }
    
    public function externalId()
    {
        return $this->responseData['id'];
    }
}