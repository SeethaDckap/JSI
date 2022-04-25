<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller\PurchaseOrder;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Epicor\Punchout\Controller\Request;

/**
 * Class Index
 */
class Index extends Request
{


    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException LocalizedException.
     */
    public function execute()
    {
        $currentTime    = $this->data['config']->getLocalDate(time(), \IntlDateFormatter::LONG);
        $transactionLog = $this->data['transactionLogs']->loadEntity();
        $transactionLog->setData('type', 'Purchase Order');
        $transactionLog->startTiming();
        $dataArray = [
            'code'      => '400',
            'error'     => '1',
            'timestamp' => $currentTime,
        ];

        $postData         = $this->getRequest()->getContent();
        $requestObj       = simplexml_load_string($postData);
        $orderHeader      = (array) $requestObj->Request->OrderRequest->OrderRequestHeader;
        $requestType      = $orderHeader['@attributes']['type'];
        $isPunchoutEnable = $this->data['config']->isPunchoutEnable();

        $dataArray['error'] = false;

        try {
            if (!$isPunchoutEnable || $requestObj === false || $requestType !== 'new') {
                if (!$isPunchoutEnable) {
                    $dataArray['error_message'] = 'Punchout feature is disabled';
                }

                $dataArray['error'] = true;
            }

            if (!$dataArray['error']) {
                $id = $this->requestValidator->validate($requestObj, true);
                if (!empty($id['error'])) {
                    $dataArray          = array_merge($dataArray, $id);
                    $dataArray['error'] = true;
                }
            }

            if ($dataArray['error']) {
                return $this->createResponse($dataArray, $transactionLog, $postData);
            }

            if (!empty($id['shopper_id'])) {
                $dataArray = [
                    'error'     => '0',
                    'code'      => '200',
                    'timestamp' => $currentTime,
                ];
                $dataArray = array_merge($dataArray, $id);

                $this->publishToQueue($requestObj, $id['connection_id'], $id['shopper_id']);

                return $this->createResponse($dataArray, $transactionLog, $postData);
            }
        } catch (\Exception $e) {

            $this->data['logger']->error($e->getMessage());
            $dataArray = [
                'code'      => '500',
                'error'     => '1',
                'timestamp' => $currentTime,
            ];

            return $this->createResponse($dataArray, $transactionLog, $postData);

        }//end try

    }//end execute()


}//end class
