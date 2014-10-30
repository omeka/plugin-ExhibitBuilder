<?php

class ExhibitBuilder_ApiImport_ExhibitPageAdapter extends ApiImport_ResponseAdapter_AbstractRecordAdapter
{
    protected $recordType = 'ExhibitPage';

    public function import()
    {
        if(!$this->record) {
            $this->record = new ExhibitPage;
        }
        $this->record->title = $this->responseData['title'];
        $this->record->slug = $this->responseData['slug'];
        $this->record->order = $this->responseData['order'];
        $this->record->exhibit_id = $this->getLocalResourceId($this->responseData['exhibit'], 'Exhibit');
        if(empty($this->responseData['parent'])) {
            $this->record->parent_id = null;
        } else {
            //first, see if it is already imported
            //if not, skip ahead and import it.
            $parentId = $this->getLocalResourceId($this->responseData['parent'], 'ExhibitPage');
            if($parentId) {
                $this->record->parent_id = $parentId;
            } else {
                $response = $this->service->exhibit_pages->get($responseData['parent']['id']);
                if($response->getStatus() == 200) {
                    $data = json_decode($response->getBody(), true);
                    $adapter = new ExhibitBuilder_ApiImport_ExhibitPageAdapter($data, $this->endpointUri);
                    $adapter->import();
                } else {
                    _log($response->getMessage());
                }
            }
        }
        try {
            $this->record->save(true);
            $this->addOmekaApiImportRecordIdMap();
        } catch(Exception $e) {
            _log($e);
        }
        $pageBlockAdapter = new ExhibitBuilder_ApiImport_ExhibitPageBlockAdapter(null, $this->endpointUri);
        foreach($this->responseData['page_blocks'] as $pageBlockData) {
            $pageBlockData['page_id'] = $this->record->id;
            $pageBlockAdapter->resetResponseData($pageBlockData);
            $pageBlock = $pageBlockAdapter->import();
        }
    }
    
    public function externalId()
    {
        return $this->responseData['id'];
    }
    
    protected function getLocalResourceId($resourceData, $type)
    {
        $remoteId = $resourceData['id'];
        $localRecord = $this->db->getTable('OmekaApiImportRecordIdMap')->localRecord($type, $remoteId, $this->endpointUri);
        return $localRecord->id;
    }
}
