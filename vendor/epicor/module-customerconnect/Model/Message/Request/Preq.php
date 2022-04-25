<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\Message\Request;

/**
 * Request PREQ - Customer Document Print
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Preq extends \Epicor\Customerconnect\Model\Message\Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('PREQ');
        $this->setConfigBase('customerconnect_enabled_messages/PREQ_request/');
    }

    /**
     * builds the SPOC message
     *
     * @return boolean
     */
    public function buildRequest()
    {
        $data = $this->getMessageTemplate();

        $data['messages']['request']['body']['print'] = $this->getPrintData();

        $this->setOutXml($data);

        return true;
    }

    public function getPrintData()
    {
        $processed = array();
        $processed[] = array(
            '_attributes' => array(
                'erpEmail' => 'N'
            ),
            'accountNumber' => $this->getAccountNumber(),
            'entityDocument' => $this->getEntityDocument(),
            'entityKey' => $this->getEntityKey(),
            'documentFormat' => '',
            'reportID' => '',
            'styleNumber' => '',
            'cultureCode' => '',
            'email' => $this->getEmailConfigs()
        );

        return $processed;
    }

    public function getEmailConfigs()
    {
        $processed = array();
        $processed[] = array(
            'emailTo' => '',
            'emailCc' => '',
            'emailBcc' => '',
            'emailSubject' => '',
            'emailBody' => ''
        );
    }

    public function processResponse()
    {
        $success = false;

        if ($this->getIsMassAction()) {
            return parent::processResponse();
        }

        if (parent::processResponse()) {
            $file = $this->getResponse()->getPrint();
            $rawData = $file ? $file->getData('data') : '';
            if (!empty($rawData)) {
                $encType = $rawData->getData('_attributes')->getEncodeType();
                $fileData = $encType == 'B' ? base64_decode($rawData->getValue()) : $rawData->getValue();
                $customerSession = $this->customerSession;
                $customerSession->unsEncodedPreqData();
                $customerSession->setEncodedPreqData(base64_encode($fileData));
                $printPathData = $this->commonFileHelper->getPrintFilePathData($fileData);
                if ($this->getAction() === 'P' && is_array($printPathData)) {
                    $success = $printPathData;
                } else {
                    $success = true;
                }
            }
        }

        return $success;
    }

    public function setStatusDescription($str)
    {
        $this->setStatusDescriptionText($str);
    }

}
