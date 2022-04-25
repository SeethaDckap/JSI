<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPOU - Supplier Purchase Order Update 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method getPurchaseOrderNumber
 * @method setPurchaseOrderNumber
 * 
 * @method setOldPurchaseOrderData
 * @method getOldPurchaseOrderData
 * 
 * @method setNewPurchaseOrderData
 * @method getNewPurchaseOrderData
 * 
 * @method getUpdateMode
 * @method setUpdateMode
 * s
 * @method getConfirmRejectValue
 * @method setConfirmRejectValue
 * 
 */
class Spou extends \Epicor\Supplierconnect\Model\Message\Request
{
    /**
     * @var int
     */
    private $_attachmentNumber = 0;

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPOU');
        $this->setConfigBase('supplierconnect_enabled_messages/SPOU_request/');
    }

    /**
     * builds the SPOU message
     * 
     * @return boolean
     */
    public function buildRequest()
    {
        $data = $this->getMessageTemplate();
        $this->_attachmentNumber = 0;

        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();
        $data['messages']['request']['body']['purchaseOrderNumber'] = $this->getPurchaseOrderNumber();

        $data = $this->doFullUpdate($data);
        $this->setOutXml($data);

        return true;
    }

    /**
     * Does a full update on a purchase order (lines included etc)
     * 
     * @param array $data
     * 
     * @return array - data to tuse for the message
     */
    public function doFullUpdate($data)
    {
        $oldPurchaseOrder = $this->getOldPurchaseOrderData();
        $purchaseOrder = $this->getNewPurchaseOrderData();
        $new = $this->getNewData();
        $old = $this->getOldData();

        $data['messages']['request']['body']['oldValues']['comment'] =
            isset($oldPurchaseOrder['comment'])?$oldPurchaseOrder['comment']:'';
        $data['messages']['request']['body']['comment'] =
            isset($purchaseOrder['comment'])?$purchaseOrder['comment']:'';
        $data['messages']['request']['body']['confirmReject'] = '';

        $attachments = $this->_processSubData('attachments', $new);
        if ($attachments) {
            $data['messages']['request']['body']['attachments'] = ['attachment' => $attachments];
        }

        $oldLines = $this->_processOldLines($oldPurchaseOrder);
        $lines = array();
        foreach ($oldLines as $lineNumber => $line) {
            $rowIndex = $line['unique_id'];
            $releasesChanged = false;
            $lineData = array();

            $newLine = $purchaseOrder['lines'][$rowIndex];

            $lineData['_attributes']['number'] = $lineNumber;
            $lineData['oldValues']['comment'] = $line['comment'];
            $lineData['comment'] = $newLine['comment'] ?: $line['comment'];

            $attachments = $this->getLineAttachments($rowIndex);
            $attachmentData = $this->_processSubData('attachments', ['attachments' => $attachments]);
           if ($attachmentData) {
                $lineData['attachments'] = ['attachment' => $attachmentData];
            }

            $lineData['releases']['release'] = array();

            if (!empty($line['releases'])) {
                foreach ($line['releases'] as $releaseNumber => $release) {
                   if(!isset($newLine['releases'][$releaseNumber])){
                       continue;
                   }
                    $newRelease = $newLine['releases'][$releaseNumber];
                    $newDate = (isset($newRelease['changed_due_date']) && $newRelease['changed_due_date']) ?
                        $this->getHelper()->getLocalDate(
                            strtotime($newRelease['changed_due_date']), \IntlDateFormatter::LONG
                        ) : '';
                    if(!isset($release['changed_promise_date'])){
                        $release['changed_promise_date'] = '';
                    }
                    $newpromiseDate = (isset($newRelease['changed_promise_date']) &&
                        $newRelease['changed_promise_date']) ?
                        $this->getHelper()->getLocalDate(
                            strtotime($newRelease['changed_promise_date']), \IntlDateFormatter::LONG
                        ) : '';
                    $newQty = ($newRelease['changed_quantity']) ?: '';
                    $newComment = ($newRelease['comment']) ?: '';

                    if (
                        !in_array($newDate, array($release['changed_due_date'], '')) ||
                        !in_array($newpromiseDate, array($release['changed_promise_date'], '')) ||
                        !in_array($newQty, array($release['changed_quantity'], '')) ||
                        !in_array($newComment, array($release['comment'], ''))
                    ) {
                        $releasesChanged = true;
                        $lineData['releases']['release'][] = array(
                            '_attributes' => array(
                                'number' => $releaseNumber
                            ),
                            'oldValues' => array(
                                'changed' => $release['changed'],
                                'changedDueDate' => $release['changed_due_date'],
                                'changedPromiseDate' => isset($release['changed_promise_date'])?
                                    $release['changed_promise_date']:'',
                                'changedQuantity' => $release['changed_quantity'],
                                'comment' => $release['comment'],
                            ),
                            'changed' => $release['changed'],
                            'changedDueDate' => $newDate,
                            'changedPromiseDate' => $newpromiseDate,
                            'changedQuantity' => $newQty,
                            'comment' => $newComment,
                        );
                    }
                }
            }

            if ($releasesChanged ||
                $newLine['comment'] != $line['comment']
            ) {
                $lines[] = $lineData;
            }
        }

        $data['messages']['request']['body']['lines']['line'] = $lines;
        return $data;
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Processes lines from the old version of the purchase order
     * 
     * @param \Epicor\Comm\Model\Xmlvarien $oldPurchaseOrder
     * 
     * @return array
     */
    private function _processOldLines($oldPurchaseOrder)
    {
        $lines = array();
        $msgLines = isset($oldPurchaseOrder['purchase_order']['lines']['line'])?
            $oldPurchaseOrder['purchase_order']['lines']['line']:[];
        if ($msgLines) {
            if(!isset($msgLines[0])){
                $olsmsgLines= $msgLines;
                $msgLines=[];
                $msgLines[]=$olsmsgLines;
            }
            foreach ($msgLines as $line) {

                $lineNumber = $line['_attributes']['number'];
                $lines[$lineNumber] = $line;
                $lines[$lineNumber]['releases'] = array();
                $releaseHolder = $line['releases'];

                if ($releaseHolder) {

                    $releases = $releaseHolder['release'];

                    if (!empty($releases) && !isset($releases[0])) {
                        $releases = array($releases);
                    }

                    if (!empty($releases)) {

                        foreach ($releases as $release) {

                            $rNumber = $release['_attributes']['number'];
                            $lines[$lineNumber]['releases'][$rNumber] = $release;
                        }
                    }
                }
            }
        }
        return $lines;
    }

    /**
     * Processes post data for  Lines / Attachments
     * into arrays for the xml
     *
     * @param string $key - post data array key to look at
     * @param array $data - posted data
     *
     * @return array
     */
    private function _processSubData($key, $data)
    {
        $this->_subData = array();

        if (isset($data[$key])) {
            if (isset($data[$key]['existing'])) {
                foreach ($data[$key]['existing'] as $rowData) {
                    $oldData = unserialize(base64_decode($rowData['old_data']));
                    unset($rowData['old_data']);
                    if ((isset($rowData['delete']) && in_array($rowData['delete'], array(1, 'on')))
                        || isset($rowData[0]['delete']) && in_array($rowData[0]['delete'], array(1, 'on'))
                    ) {
                        $this->addSubDataArray($key, 'R', $oldData, $oldData);
                    } else {
                        if ($rowData != $oldData) {
                            $this->addSubDataArray($key, 'U', $rowData, $oldData);
                        } else {
                            // possibly set a different flag here: TBC
                            $this->addSubDataArray($key, 'U', $rowData, $oldData);
                        }
                    }
                }
            }

            if (isset($data[$key]['new'])) {
                foreach ($data[$key]['new'] as $rowIndex => $rowData) {
                    $combiValue = '';
                    foreach ($rowData as $rowKey => $rowVal) {
                        if (!is_object($rowVal)) {
                            $combiValue .= $rowVal;
                        }
                    }
                    if (!empty($combiValue)) {
                        $this->addSubDataArray($key, 'A', $rowData);
                    }
                }
            }
        }

        return $this->_subData;
    }

    /**
     * Gets an individual row of data for the message
     * For Price breaks / Supplier UOM / Cross reference parts repeating groups
     *
     * @param $rowIndex
     * @param string $type - type of array to build
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     */
    private function addSubDataArray($type, $action, $newData, $oldData = null)
    {
        switch ($type) {
            case 'attachments':
                $this->addAttachmentArray($action, $newData, $oldData);
                break;
        }
    }

    /**
     * @param $action
     * @param $newData
     * @param null $oldData
     */
    private function addAttachmentArray($action, $newData, $oldData = null)
    {
        $erpFileId = isset($newData['erp_file_id']) ? $newData['erp_file_id'] :
            (isset($oldData['erp_file_id']) ? $oldData['erp_file_id'] : '');
        $webFileId = isset($newData['web_file_id']) ? $newData['web_file_id'] :
            (isset($oldData['web_file_id']) ? $oldData['web_file_id'] : '');
        $desc = isset($newData['description']) ? $newData['description'] :
            (isset($oldData['description']) ? $oldData['description'] : '');
        $filename = isset($newData['filename']) ? $newData['filename'] :
            (isset($oldData['filename']) ? $oldData['filename'] : '');
        $url = isset($newData['url']) ? $newData['url'] :
            (isset($oldData['url']) ? $oldData['url'] : '');
        $status = isset($newData['attachment_status']) ? $newData['attachment_status'] :
            (isset($oldData['attachment_status']) ? $oldData['attachment_status'] : '');

        $number = isset($oldData['attachment_number']) ? $oldData['attachment_number'] : $this->_attachmentNumber;

        $this->_attachmentNumber = ($number >= $this->_attachmentNumber) ? $number + 1 : $this->_attachmentNumber;

        $attachment = array(
            '_attributes' => array(
                'action' => $action
            ),
            'attachmentNumber' => $number,
            'erpFileId' => $erpFileId,
            'webFileId' => $webFileId,
            'description' => $desc,
            'filename' => $filename,
            'url' => $url,
            'attachmentStatus' => $status,
        );

        if (!empty($oldData) && (is_array($newData) && count($newData) > 1)) {
            $attachment['oldAttachment'] = array(
                'attachmentNumber' => isset($oldData['attachment_number']) ? $oldData['attachment_number'] : '',
                'erpFileId' => isset($oldData['erp_file_id']) ? $oldData['erp_file_id'] : '',
                'webFileId' => isset($oldData['web_file_id']) ? $oldData['web_file_id'] : '',
                'description' => isset($oldData['description']) ? $oldData['description'] : '',
                'filename' => isset($oldData['filename']) ? $oldData['filename'] : '',
                'url' => isset($oldData['url']) ? $oldData['url'] : '',
                'attachmentStatus' => isset($oldData['attachment_status']) ? $oldData['attachment_status'] : '',
            );
        }

        $this->_subData[] = $attachment;
    }

    /**
     * @param $rowIndex
     * @return array
     */
    private function getLineAttachments($rowIndex)
    {
        $new = $this->getNewData();
        $attachments = [];

        if (isset($new['lineattachments']['existing'][$rowIndex])) {
            $attachments['existing'] = $new['lineattachments']['existing'][$rowIndex];
        }

        if (isset($new['lineattachments']['new'][$rowIndex])) {
            $attachments['new'] = $new['lineattachments']['new'][$rowIndex];
        }
        return $attachments;
    }
}
