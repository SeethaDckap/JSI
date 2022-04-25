<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPCC - Supplier Purchase Order Changes Confirm/Reject
 *  
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method getActions
 * @method setActions
 * 
 */
class Spcc extends \Epicor\Supplierconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPCC');
        $this->setConfigBase('supplierconnect_enabled_messages/SPCC_request/');
    }

    /**
     * builds the SPCC message
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

        $purchaseOrdersMsg['purchaseOrder'] = $this->_processPurchaseOrders();

        $data['messages']['request']['body']['purchaseOrders'] = $purchaseOrdersMsg;

        $this->setOutXml($data);

        return true;
    }

    /**
     * Processes purchase orders passed to th emessage into the required format for the message
     * 
     * @return array
     */
    private function _processPurchaseOrders()
    {

        $processed = array();

        $actions = $this->getActions();

        if (!empty($actions)) {
            foreach ($actions as $po => $lineReleases) {
                $purchaseOrder = array(
                    'purchaseOrderNumber' => $po,
                    'lines' => array(
                        'line' => array()
                    )
                );
                foreach ($lineReleases as $lineNum => $release) {
                    $line = array(
                        'purchaseOrderLineNumber' => $lineNum,
                        'releases' => array()
                    );

                    foreach ($release as $number => $action) {
                        $line['releases']['release'][] = array(
                            'releaseNumber' => $number,
                            'confirmReject' => $action
                        );
                    }

                    $purchaseOrder['lines']['line'][] = $line;
                }

                $processed[] = $purchaseOrder;
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
