<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\Quote\Payment;

use Epicor\Customerconnect\Model\ArPayment\Quote\Payment;
use Epicor\Customerconnect\Api\OrderPaymentRepositoryInterface as OrderPaymentRepository;
use Epicor\Customerconnect\Api\Data\OrderPaymentInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Payment\Model\Method\Substitution;

/**
 * Class ToOrderPayment
 */
class ToOrderPayment
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderPaymentRepository
     */
    protected $orderPaymentRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    protected $paymentData;    

    /**
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderPaymentRepository $orderPaymentRepository,
        Copy $objectCopyService,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->paymentData = $paymentData;
    }

    /**
     * @param Payment $object
     * @param array $data
     * @return OrderPaymentInterface
     */
    public function convert(Payment $object, $data = [])
    {
        $paymentData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_payment',
            'to_order_payment',
            $object
        );
        
        $method= $this->paymentData->getMethodInstance($object->getMethod());

        $orderPayment = $this->orderPaymentRepository->create();
        $this->dataObjectHelper->populateWithArray(
            $orderPayment,
            array_merge($paymentData, $data),
            \Epicor\Customerconnect\Api\Data\OrderPaymentInterface::class
        );
        $orderPayment->setAdditionalInformation(
            array_merge(
                $object->getAdditionalInformation(),
                [Substitution::INFO_KEY_TITLE => $method->getTitle()]
            )
        );
        
        $baseUrl = $this->storeManager->getStore($object->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $object->setEccSiteUrl($baseUrl)->save();
        // set directly on the model
        $orderPayment->setCcNumber($object->getCcNumber());
        $orderPayment->setCcCid($object->getCcCid());
        $orderPayment->setEccElementsProcessorId($object->getEccElementsProcessorId());
        $orderPayment->setEccElementsTransactionId($object->getEccElementsTransactionId());
        $orderPayment->setEccElementsPaymentAccountId($object->getEccElementsPaymentAccountId());
        $orderPayment->setEccCcCvvStatus($object->getEccCcCvvStatus());
        $orderPayment->setEccCcAuthCode($object->getEccCcAuthCode());    
        $orderPayment->setEccSiteUrl($object->getEccSiteUrl());
        $orderPayment->setEccIsSaved($object->getEccIsSaved());
        $orderPayment->setCcLast4($object->getCcLast4());
        $orderPayment->setCcExpMonth($object->getCcExpMonth());
        $orderPayment->setCcExpYear($object->getCcExpYear());
        $orderPayment->setCcType($object->getCcType());
        $orderPayment->setLastTransId($object->getLastTransId());
        $orderPayment->setCcTransId($object->getCcTransId());
        $orderPayment->setEccCcvToken($object->getEccCcvToken());
        $orderPayment->setEccCvvToken($object->getEccCvvToken());     
        return $orderPayment;
    }
}
