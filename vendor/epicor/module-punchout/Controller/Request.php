<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller;

use Epicor\Punchout\Controller\Adminhtml\Transactionlogs;
use Epicor\Punchout\Model\Request\Validators\RequestValidator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Request
 */
abstract class Request extends Action implements CsrfAwareActionInterface
{

    const ATTRIBUTE = '@attributes';

    /**
     * Request
     *
     * @var Http
     */
    private $request;

    /**
     * FormKey
     *
     * @var FormKey
     */
    private $formKey;

    /**
     * Request Validator.
     *
     * @var RequestValidator
     */
    protected $requestValidator;

    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Raw factory.
     *
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * Data array.
     *
     * @var array
     */
    protected $data = [];


    /**
     * Index constructor.
     *
     * @param Context          $context           Context.
     * @param FormKey          $formKey           FormKey.
     * @param Http             $request           Request.
     * @param RequestValidator $requestValidator  RequestValidator.
     * @param PageFactory      $resultPageFactory Page Factory.
     * @param RawFactory       $resultRawFactory  Raw factory.
     * @param array            $data              Data.
     *
     * @throws LocalizedException LocalizedException.
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        Http $request,
        RequestValidator $requestValidator,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        array $data=[]
    ) {
        $this->request           = $request;
        $this->formKey           = $formKey;
        $this->requestValidator  = $requestValidator;
        $this->data              = $data;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory  = $resultRawFactory;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        parent::__construct(
            $context
        );

    }//end __construct()


    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;

    }//end createCsrfValidationException()


    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;

    }//end validateForCsrf()


    /**
     * Save Transaction Log.
     *
     * @param Transactionlogs $transactionLog       Log model.
     * @param array           $postData             Post Data.
     * @param string          $cxmlResponse         CXML response.
     * @param array           $saveInTransactionLog Log array.
     *
     * @return void
     */
    public function saveTransactionLog($transactionLog, $postData, $cxmlResponse, $saveInTransactionLog)
    {
        $connectionId       = !empty($saveInTransactionLog['connection_id']) ? $saveInTransactionLog['connection_id'] : '';
        $transactionLogData = ['type' => $transactionLog->getData('type')];
        $transactionLogData['source_url']     = $this->_redirect->getRefererUrl();
        $transactionLogData['target_url']     = $this->data['url']->getUrl('*/*/*');
        $transactionLogData['connection_id']  = $connectionId;
        $transactionLogData['cxml_request']   = $postData;
        $transactionLogData['cxml_response']  = $cxmlResponse;
        $transactionLogData['message_code']   = $saveInTransactionLog['code'];
        $transactionLogData['message_status'] = $saveInTransactionLog['text'];
        $transactionLog->endTiming();
        $transactionLog->addData($transactionLogData);
        $this->data['transactionLogs']->save($transactionLog);

    }//end saveTransactionLog()


    /**
     * Create response.
     *
     * @param array           $dataArray      Data array.
     * @param transactionlogs $transactionLog Log model.
     * @param array           $postData       Post data.
     *
     * @return Raw
     */
    public function createResponse($dataArray, $transactionLog, $postData)
    {
        $factoryObject = $this->resultRawFactory->create();
        $responseData  = $this->data['response']->prepareData($dataArray);
        $cxmlResponse  = $this->data['response']->sendResponse($responseData);
        $factoryObject->setContents($cxmlResponse);
        $errorCode            = $this->data['response']->getErrorMessage($dataArray);
        $saveInTransactionLog = array_merge($errorCode, $dataArray);
        $this->saveTransactionLog($transactionLog, $postData, $cxmlResponse, $saveInTransactionLog);
        return $factoryObject;

    }//end createResponse()


    /**
     * Publish to queue.
     *
     * @param \SimpleXMLElement $requestObj   Request object.
     * @param integer           $connectionId Connection ID.
     * @param integer           $shopperId    Shopper ID.
     */
    public function publishToQueue($requestObj, $connectionId, $shopperId)
    {
        $publisher           = $this->data['publisher'];
        $purchaseOrder       = $this->data['purchaseOrder'];
        $itemPublish         = $this->getItemsArray($requestObj);
        $shippingAddressCode = $this->getAddressCode($requestObj);
        $methodCode          = $this->getMethodCode($requestObj);
        $totals              = $this->getTotals($requestObj);
        $orderId             = $this->getOrderId($requestObj);

        $purchaseOrder->setConnectionId($connectionId);
        $purchaseOrder->setItemArray($itemPublish);
        $purchaseOrder->setCustomerId($shopperId);
        $purchaseOrder->setShippingAddressCode($shippingAddressCode);
        $purchaseOrder->setMethodCode([$methodCode]);
        $purchaseOrder->setOrderId($orderId);
        $purchaseOrder->setTotals([$totals]);

        $publisher->publish('ecc.punchout.ordercreate', $purchaseOrder);

    }//end publishToQueue()


    /**
     * Get items array.
     *
     * @param \SimpleXMLElement $requestObj Request object.
     *
     * @return array
     */
    public function getItemsArray(\SimpleXMLElement $requestObj)
    {
        $itemPublish = [];
        $itemArray   = $requestObj->Request->OrderRequest->ItemOut;

        if (!empty((array) $itemArray)) {
            foreach ($itemArray as $item) {
                $extrinsic = $item->ItemDetail->Extrinsic;
                $extrinsic = $this->getExtrinsicData($extrinsic);

                $item  = (array) $item;
                $sku   = (array) $item['ItemID']->SupplierPartID;
                $uom   = (array) $item['ItemDetail']->UnitOfMeasure;
                $price = (array) $item['ItemDetail']->UnitPrice->Money;
                $tax   = isset($item['Tax']) ? (array) $item['Tax']->Money : [0];

                $arr = [
                    'qty'          => $item[self::ATTRIBUTE]['quantity'],
                    'sku'          => $sku[0],
                    'uom'          => $uom[0],
                    'price'        => $price[0],
                    'tax'          => $tax[0],
                    'locationCode' => $extrinsic['locationCode'],
                    'ewaCode'      => $extrinsic['ewaCode'],
                ];
                array_push($itemPublish, $arr);
            }
        }

        return $itemPublish;

    }//end getItemsArray()


    /**
     * Get shipping address code.
     *
     * @param \SimpleXMLElement $requestObj
     *
     * @return \SimpleXMLElement|string
     */
    public function getAddressCode(\SimpleXMLElement $requestObj)
    {
        $shipTo = (array) $requestObj->Request->OrderRequest->OrderRequestHeader->ShipTo->Address;
        if ($shipTo) {
            return $shipTo[self::ATTRIBUTE]['addressID'];
        }

        return '';

    }//end getAddressCode()


    /**
     * Get shipping method code.
     *
     * @param \SimpleXMLElement $requestObj
     *
     * @return array
     */
    public function getMethodCode(\SimpleXMLElement $requestObj)
    {
        $code   = (array) $requestObj->Request->OrderRequest->OrderRequestHeader->Shipping->Description;
        $amount = (array) $requestObj->Request->OrderRequest->OrderRequestHeader->Shipping->Money;

        return [
            'code' => isset($code[0]) ? $code[0] : '',
            'amt'  => isset($amount[0]) ? $amount[0] : 0,
        ];

    }//end getMethodCode()


    /**
     * Get totals.
     *
     * @param \SimpleXMLElement $requestObj
     *
     * @return array
     */
    public function getTotals(\SimpleXMLElement $requestObj)
    {
        $totalInc = (array) $requestObj->Request->OrderRequest->OrderRequestHeader->Total->Money;
        $tax      = (array) $requestObj->Request->OrderRequest->OrderRequestHeader->Tax->Money;
        return [
            'totalInc' => $totalInc ?: 0,
            'tax'      => $tax ?: 0,
        ];

    }//end getTotals()


    /**
     * Get order ID.
     *
     * @param \SimpleXMLElement $requestObj
     *
     * @return mixed
     */
    public function getOrderId(\SimpleXMLElement $requestObj)
    {
        $header = (array) $requestObj->Request->OrderRequest->OrderRequestHeader;

        return $header[self::ATTRIBUTE]['orderID'];

    }//end getOrderId()


    /**
     * @param \SimpleXMLElement $extrinsic
     *
     * @return array
     */
    public function getExtrinsicData(\SimpleXMLElement $extrinsic)
    {
        $locationCode = '';
        $ewaCode      = '';

        if (!empty($extrinsic)) {
            foreach ($extrinsic as $v) {
                if ((string) $v->attributes()['name'] === 'locationCode') {
                    $locationCode = (string) $v;
                }

                if ((string) $v->attributes()['name'] === 'ewaCode') {
                    $ewaCode = (string) $v;
                }
            }
        }

        return [
            'locationCode' => $locationCode,
            'ewaCode'      => $ewaCode
        ];

    }//end getExtrinsicData()


}//end class

