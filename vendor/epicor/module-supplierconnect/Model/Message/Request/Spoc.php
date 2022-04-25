<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPOC - Supplier Purchase Order Confirm/Reject
 *  
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method getConfirmed
 * @method setConfirmed
 * 
 * @method setRejected
 * @method getRejected
 * 
 * @method setPurchaseOrderData
 * @method getPurchaseOrderData
 * 
 */
class Spoc extends \Epicor\Supplierconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPOC');
        $this->setConfigBase('supplierconnect_enabled_messages/SPOC_request/');
    }

    /**
     * builds the SPOC message
     * 
     * @return boolean
     */
    public function buildRequest()
    {
        $data = $this->getMessageTemplate();

        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();

        $purchaseOrdersMsg = array(
            'purchaseOrder' => array()
        );

        $purchaseOrders = $this->getPurchaseOrderData();

        $confirmed = $this->getConfirmed();
        if ($confirmed) {
            $confirmedPo = $this->_processPurchaseOrders('C', $confirmed);
            $purchaseOrdersMsg['purchaseOrder'] = $confirmedPo;
        }

        $rejected = $this->getRejected();
        if ($rejected) {
            $rejectedPo = $this->_processPurchaseOrders('R', $rejected);
            $purchaseOrdersMsg['purchaseOrder'] = array_merge($purchaseOrdersMsg['purchaseOrder'], $rejectedPo);
        }

        $data['messages']['request']['body']['purchaseOrders'] = $purchaseOrdersMsg;

        $this->setOutXml($data);

        return true;
    }

    private function _processPurchaseOrders($action, $purchaseOrders)
    {

        $poData = $this->getPurchaseOrderData();

        $processed = array();

        if (!empty($purchaseOrders)) {
            foreach ($purchaseOrders as $po) {
                $processed[] = array(
                    'purchaseOrderNumber' => $po,
                    'confirmReject' => $action,
                    'oldValues' => array(
                        'orderDate' => $poData[$po]['order_date'],
                        'orderStatus' => $poData[$po]['order_status'],
                        'orderConfirmed' => $poData[$po]['order_confirmed'],
                    )
                );
            }
        }

        return $processed;
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            return true;
        } else {
            return false;
        }
    }

}
