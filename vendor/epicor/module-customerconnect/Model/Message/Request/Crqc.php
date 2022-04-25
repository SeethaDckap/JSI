<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CRQC - Customer Quote Confirm / Reject  
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Crqc extends \Epicor\Customerconnect\Model\Message\Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CRQC');
        $this->setConfigBase('customerconnect_enabled_messages/CRQC_request/');
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

        $rfqsMsg = array(
            'quote' => array()
        );

        $confirmed = $this->getConfirmed();
        if ($confirmed) {
            $confirmedPo = $this->_processRfqs('C', $confirmed);
            $rfqsMsg['quote'] = $confirmedPo;
        }

        $rejected = $this->getRejected();
        if ($rejected) {
            $rejectedPo = $this->_processRfqs('R', $rejected);
            $rfqsMsg['quote'] = array_merge($rfqsMsg['quote'], $rejectedPo);
        }

        $data['messages']['request']['body']['quotes'] = $rfqsMsg;

        $this->setOutXml($data);

        return true;
    }

    private function _processRfqs($action, $rfqs)
    {

        $rfqData = $this->getRfqData();

        $processed = array();

        if (!empty($rfqs)) {
            foreach ($rfqs as $rfq) {
                $processed[] = array(
                    'quoteNumber' => $rfqData[$rfq]['quote_number'],
                    'quoteSequence' => $rfqData[$rfq]['quote_sequence'],
                    'recurringQuote' => $rfqData[$rfq]['recurring_quote'],
                    'confirmReject' => $action,
                    'customerReference' => $rfqData[$rfq]['customer_reference'],
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
