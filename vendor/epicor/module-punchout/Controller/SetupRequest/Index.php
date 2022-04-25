<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller\SetupRequest;

use Epicor\Punchout\Controller\Request;

/**
 * Class Index
 *
 * @package Epicor\Punchout\Controller\SetupRequest
 */
class Index extends Request
{


    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     */
    public function execute()
    {
        $currentTime    = $this->data['config']->getLocalDate(time(), \IntlDateFormatter::LONG);
        $transactionlog = $this->data['transactionLogs']->loadEntity();
        $transactionlog->setData('type', 'PunchOut Setup Request');
        $transactionlog->startTiming();
        $dataArray = [
            'code'      => '400',
            'error'     => '1',
            'timestamp' => $currentTime,
        ];

        $postData         = $this->getRequest()->getContent();
        $requestObj       = simplexml_load_string($postData);
        $isPunchoutEnable = $this->data['config']->isPunchoutEnable();
        if (!$isPunchoutEnable || $requestObj === false) {
            if (!$isPunchoutEnable) {
                $dataArray['error_message'] = 'Punchout feature is disabled';
            }

            return $this->createResponse($dataArray, $transactionlog, $postData);
        }

        $id = $this->requestValidator->validate($requestObj);
        if (!empty($id['error'])) {
            $dataArray = array_merge($dataArray, $id);
            return $this->createResponse($dataArray, $transactionlog, $postData);
        }

        if (!empty($id['shopper_id'])) {
            return $this->sendTokenResponse($requestObj, $dataArray, $transactionlog, $postData, $id);
        }

    }//end execute()


    /**
     * @param \SimpleXMLElement                  $requestObj     CXML Request object.
     * @param array                              $dataArray      Data Array.
     * @param TransactionlogsRepositoryInterface $transactionlog Transactionlogs Repository Interface.
     * @param array                              $postData       Post Data.
     * @param array                              $id             Validation array data.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function sendTokenResponse($requestObj, $dataArray, $transactionlog, $postData, $id)
    {
        $itemData          = $requestObj->Request->PunchOutSetupRequest;
        $punchoutOperation = $this->data['operation']->processOperation($itemData, $id['shopper_id'], $id['identity']);
        if ($punchoutOperation['error']) {
            $dataArray = array_merge($dataArray, $punchoutOperation);
            return $this->createResponse($dataArray, $transactionlog, $postData);
        }

        $token       = $this->data['tokenBuilder']->build($requestObj, $id);
        $cartId      = base64_encode(serialize($punchoutOperation['punchoutCart']));
        $cartIdToUrl = '';
        if (!empty($cartId)) {
            if (!empty($punchoutOperation['notAddedProd'])) {
                $errorProd   = base64_encode(implode(",", $punchoutOperation['notAddedProd']));
                $cartIdToUrl = '/errorIds/'.$errorProd;
            }
            $cartIdToUrl .= '/cartId/'.$cartId;
        }

        $dataArray  = [
            'error'     => '0',
            'code'      => '200',
            'url'       => $this->data['url']->getUrl(
                $id['website_url'].'punchout/setuprequest/sessionstart/tokenid/'.$token.$cartIdToUrl
            ),
            'timestamp' => $this->data['config']->getLocalDate(time(), \IntlDateFormatter::LONG),
        ];
        $dataArray = array_merge($dataArray, $id);
        return $this->createResponse($dataArray, $transactionlog, $postData);
    }


}//end class
