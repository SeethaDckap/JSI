<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Epicor\Comm\Helper\Data;
use Epicor\Comm\Model\Customer;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\AddressFactory;

/**
 * Quote to order.
 *
 */
class AddressHandler
{

    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var AddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var Data
     */
    private $commHelper;

    /**
     * @var CollectionFactory
     */
    private $customerResourceModelAddressCollectionFactory;

    /**
     * @var Customer
     */
    private $customerModel;


    /**
     * Constructor.
     *
     * @param AddressFactory $quoteAddressFactory
     * @param CollectionFactory $customerResourceModelAddressCollectionFactory
     * @param Data $commHelper
     * @param Customer $customerModel
     */
    public function __construct(
        AddressFactory $quoteAddressFactory,
        CollectionFactory $customerResourceModelAddressCollectionFactory,
        Data $commHelper,
        customer $customerModel
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->customerResourceModelAddressCollectionFactory = $customerResourceModelAddressCollectionFactory;
        $this->commHelper = $commHelper;
        $this->customerModel = $customerModel;

    }//end __construct()


    /**
     * Update quote addresses.
     *
     * @param CartInterface $quote
     * @param string $shippingAddressCode
     * @param integer $customerId
     *
     * @return CartInterface
     */
    public function updateAddresses(CartInterface $quote, string $shippingAddressCode, $customerId)
    {
        $this->quote  = $quote;
        $customer     = $this->customerModel->load($customerId);
        $erpAccountId = $customer->getEccErpaccountId();

        $this->quote->setEccErpAccountId($erpAccountId);

        $erpAccountInfo             = $this->commHelper->getErpAccountInfo($erpAccountId);
        $defaultDeliveryAddressCode = $erpAccountInfo->getDefaultDeliveryAddressCode();
        $defaultInvoiceAddressCode  = $erpAccountInfo->getDefaultInvoiceAddressCode();

        $this->updateShippingAddress($erpAccountInfo, $shippingAddressCode, $defaultDeliveryAddressCode);
        $this->updateBillingAddress($defaultInvoiceAddressCode);

        return $this->quote;

    }//end updateAddresses()


    /**
     * Update shipping address.
     *
     * @param Customer\Erpaccount $erpAccountInfo
     * @param string $shippingAddressCode
     * @param string $defaultAddressCode
     */
    private function updateShippingAddress(Customer\Erpaccount $erpAccountInfo, string $shippingAddressCode, string $defaultAddressCode)
    {
        $erpShippingAddress = $erpAccountInfo->getAddress($shippingAddressCode);

        if ($erpShippingAddress) {
            $shippingAddress = $this->customerResourceModelAddressCollectionFactory->create()
                ->addFieldToFilter('ecc_erp_address_code', $shippingAddressCode)
                ->getFirstItem();

            if (!$shippingAddress->isObjectNew()) {
                $shippingAddress = $shippingAddress->getData();
                $quoteAddress    = $this->quoteAddressFactory->create();
                $quoteAddress->setData($shippingAddress);
                $this->quote->setShippingAddress($quoteAddress);
            } else {
                $this->quote->getShippingAddress()->setEccErpAddressCode($defaultAddressCode);
            }

        } else {
            $this->quote->getShippingAddress()->setEccErpAddressCode($defaultAddressCode);
        }


    }//end updateShippingAddress()


    /**
     * Update billing address
     *
     * @param string $defaultAddressCode
     */
    public function updateBillingAddress(string $defaultAddressCode)
    {
        $this->quote->getBillingAddress()->setEccErpAddressCode($defaultAddressCode);
    }


}//end class

