<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Observer
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Observer\Gor;

use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\Punchout\Model\Shipping;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AlterMethodCode
 */
class AlterMethodCode implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Shipping model.
     *
     * @var Shipping
     */
    private $shippingModel;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Scope config.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * Constructor.
     *
     * @param Shipping                $shippingModel   Shipping model.
     * @param CartRepositoryInterface $quoteRepository Quote repository.
     * @param ScopeConfigInterface    $scopeConfig     Scope config.
     */
    public function __construct(
        Shipping $shippingModel,
        CartRepositoryInterface $quoteRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->shippingModel   = $shippingModel;
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig     = $scopeConfig;

    }//end __construct()


    /**
     * Execute function.
     *
     * @param Observer $observer Event observer.
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $requestData = $observer->getEvent()->getRequestData();
        $messageBody = $requestData->getMessages();
        $order       = $observer->getEvent()->getOrder();
        $quoteId     = $order->getQuoteId();
        $quote       = $this->quoteRepository->get($quoteId);
        if ($quote->getIsPunchout()) {
            $shippingDescription = $order->getShippingDescription();
            $shippingCode        = explode('-', $shippingDescription);

            if (isset($shippingCode[1])) {
                $shippingCode    = trim($shippingCode[1]);
                $erpShippingCode = $this->shippingModel->getErpMapping($shippingCode, $quote->getEccPunchoutConnectionId());
            } else {
                $defaultCode     = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_shipping_code', ScopeInterface::SCOPE_STORE);
                $erpShippingCode = $defaultCode ? : '';
            }

            $messageBody['request']['body']['delivery']['methodCode'] = $erpShippingCode;
            $requestData->setMessages($messageBody);
        }

    }//end execute()


}//end class
