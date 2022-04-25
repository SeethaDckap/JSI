<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SURU - Supplier Rfq Update  
 * 
 * @method setRfqNumber()
 * @method getRfqNumber()
 * @method setLine()
 * @method getLine()
 * @method getNewData()
 * @method setNewData()
 * @method setOldData()
 * @method getOldData()
 * 
 */
class Suru extends \Epicor\Supplierconnect\Model\Message\Request
{

    private $_subData = array();
    private $_attachmentNumber = 0;

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SURU');
        $this->setConfigBase('supplierconnect_enabled_messages/SURU_request/');
    }

    public function buildRequest()
    {
        //   don't proceed if account number not valid 
        if ($this->getAccountNumber()) {
            $this->_attachmentNumber = 0;
            $message = $this->getMessageTemplate();

            $new = $this->getNewData();
            $old = $this->getOldData();

            $message['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
            $message['messages']['request']['body']['rfqNumber'] = $this->getRfqNumber();
            $message['messages']['request']['body']['line'] = $this->getLine();

            $rfq = array(
                'oldValues' => array(
                    'respondDate' => $old['respond_date'],
                    'decisionDate' => $old['decision_date'],
                    'effectiveDate' => $old['effective_date'],
                    'expiresDate' => $old['expires_date'],
                    'leadDays' => $old['lead_days'],
                    'quantityOnHand' => $old['quantity_on_hand'],
                    'reference' => $old['reference'],
                    'minimumPrice' => $old['minimum_price'],
                    'baseUnitPrice' => $old['base_unit_price'],
                    'pricePer' => $old['price_per'],
                    'discountPercent' => $old['discount_percent'],
                    'priceBreakModifier' => $old['price_break_modifier'],
                    'priceComments' => $old['price_comments'],
                ),
                'respondDate' => $old['respond_date'],
                'decisionDate' => $old['decision_date'],
                'effectiveDate' => $old['effective_date'],
                'expiresDate' => $this->getHelper()->getLocalDate(strtotime($new['expires_date']), \IntlDateFormatter::LONG),
                'leadDays' => $new['lead_days'],
                'quantityOnHand' => $new['quantity_on_hand'],
                'reference' => $new['reference'],
                'minimumPrice' => $new['minimum_price'],
                'baseUnitPrice' => $new['base_unit_price'],
                'pricePer' => $new['price_per'],
                'discountPercent' => $new['discount_percent'],
                'priceBreakModifier' => $new['price_break_modifier'],
                'priceComments' => $new['price_comments'],
                'unitOfMeasureDescription' => $old['unit_of_measure_description'],
                'crossReferenceParts' => array(),
                'supplierUnitOfMeasures' => array(),
                'priceBreaks' => array()
            );

            $attachments = $this->_processSubData('attachments', $new);
            if ($attachments) {
                $rfq['attachments'] = array('attachment' => $attachments);
            }
            $breaks = $this->_processSubData('price_breaks', $new);
            if ($breaks) {
                $rfq['priceBreaks'] = array('priceBreak' => $breaks);
            }
            $suoms = $this->_processSubData('supplier_unit_of_measures', $new);
            if ($suoms) {
                $rfq['supplierUnitOfMeasures'] = array('supplierUnitOfMeasure' => $suoms);
            }

            $xref = $this->_processSubData('cross_reference_parts', $new);
            if ($xref) {
                $rfq['crossReferenceParts'] = array('crossReferencePart' => $xref);
            }

            $message['messages']['request']['body']['rfq'] = $rfq;

            $this->setOutXml($message);
            return true;
        } else {
            return 'Missing account number';
        }
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
     * Processes post data for Price breaks / Supplier UOM / Cross reference parts
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
                    if (isset($rowData['delete']) || isset($rowData[0]['delete'])) {
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
     * @param string $type - type of array to build
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     */
    private function addSubDataArray($type, $action, $newData, $oldData = null)
    {
        switch ($type) {
            case 'attachments':
                $this->addAttachmentArray($action, $newData, $oldData);
                break;
            case 'price_breaks':
                $this->addPriceBreakArray($action, $newData, $oldData);
                break;
            case 'supplier_unit_of_measures':
                $this->addSupplierUnitOfMeasureArray($action, $newData, $oldData);
                break;
            case 'cross_reference_parts':
                $this->addCrossReferencePartArray($action, $newData, $oldData);
                break;
        }
    }

    /**
     * builds a price break array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addPriceBreakArray($action, $newData, $oldData = null)
    {
        $priceBreak = array(
            '_attributes' => array(
                'action' => $action
            ),
            'oldPriceBreak' => array(),
            'priceBreakCode' => isset($newData['price_break_code']) ? $newData['price_break_code'] : '',
            'quantity' => $newData['quantity'],
            'daysOut' => $newData['days_out'],
            'modifier' => $newData['modifier'],
            'effectivePrice' => $newData['effective_price'],
        );

        if (!empty($oldData)) {
            $priceBreak['oldPriceBreak'] = array(
                'priceBreakCode' => $oldData['price_break_code'],
                'quantity' => $oldData['quantity'],
                'daysOut' => $oldData['days_out'],
                'modifier' => $oldData['modifier'],
                'effectivePrice' => $oldData['effective_price'],
            );
        }

        $this->_subData[] = $priceBreak;
    }

    /**
     * builds a Supplier UOM array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addSupplierUnitOfMeasureArray($action, $newData, $oldData = null)
    {
        $uom = array(
            '_attributes' => array(
                'action' => $action
            ),
            'unitOfMeasure' => $newData['unit_of_measure'],
            'conversionFactor' => $newData['conversion_factor'],
            'oldSupplierUnitofMeasure' => array(),
            'operator' => $newData['operator'],
            'value' => $newData['value'],
            'result' => $newData['result'],
        );

        if (!empty($oldData)) {
            $uom['oldSupplierUnitofMeasure'] = array(
                'operator' => $oldData['operator'],
                'value' => $oldData['value'],
                'result' => $oldData['result'],
            );
        }

        $this->_subData[] = $uom;
    }

    /**
     * builds a cross reference part array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addCrossReferencePartArray($action, $newData, $oldData = null)
    {
        if ($action == 'U') {
            if ($newData['manufacturer_code'] != $oldData['manufacturer_code'] ||
                $newData['manufacturers_product_code'] != $oldData['manufacturers_product_code'] ||
                $newData['supplier_product_code'] != $oldData['supplier_product_code']) {
                $this->addCrossReferencePartArray('D', $oldData, $oldData);
                $this->addCrossReferencePartArray('A', $newData);
                return;
            }
        }

        $part = array(
            '_attributes' => array(
                'action' => $action
            ),
            'oldCrossReferencePart' => array(),
            'manufacturerCode' => $newData['manufacturer_code'],
            'manufacturersProductCode' => $newData['manufacturers_product_code'],
            'supplierProductCode' => $newData['supplier_product_code'],
            'supplierLeadDays' => $newData['supplier_lead_days'],
            'supplierReference' => $newData['supplier_reference'],
        );

        if (!empty($oldData)) {
            $part['oldCrossReferencePart'] = array(
                'manufacturerCode' => $oldData['manufacturer_code'],
                'manufacturersProductCode' => $oldData['manufacturers_product_code'],
                'supplierProductCode' => $oldData['supplier_product_code'],
                'supplierLeadDays' => $oldData['supplier_lead_days'],
                'supplierReference' => $oldData['supplier_reference'],
            );
        }

        $this->_subData[] = $part;
    }

    /**
     * builds attachment array
     *
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     *
     * @return type
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

}
