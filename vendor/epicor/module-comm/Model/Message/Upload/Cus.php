<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * 
 * @method Epicor_Comm_Model_Customer_Erpaccount getCusErpAccount() Returns Erp Account Created/Updated by the CUS
 * @method setCusErpAccount(Epicor_Comm_Model_Customer_Erpaccount $account)
 * 
 * Response CUS - Upload Customer Record
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Cus extends \Epicor\Comm\Model\Message\Upload
{

    /**
     * @var \Epicor\Comm\Helper\Messaging\CustomerFactory
     */
    protected $commMessagingCustomerHelper;
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Helper\Messaging\CustomerFactory $commMessagingCustomerHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/cus_mapping/');
        $this->setMessageType('CUS');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_erpaccount', true, true);
        $this->registry->register('entity_register_update_erpaddress', true, true);

    }
    public function processAction()
    {
        $customer = $this->getRequest()->getCustomer();
        if (!$customer->getAccountId())
            $customer->setAccountId($customer->getAccountNumber());

        $customer->setOrigAccountNumber($customer->getAccountNumber());

        $helper = $this->commMessagingCustomerHelper->create();

        $brands = $customer->getBrands();
        $brand = null;
        if (!is_null($brands))
            $brand = $brands->getBrand();

        if (is_array($brand))
            $brand = $brand[0];

        if (empty($brand) || !$brand->getCompany())
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End

        $company = $brand->getCompany();

        $customer->setBrandCompany($company);
        if (!empty($company)) {
            $delimiter = $helper->getUOMSeparator();
            $customer->setAccountNumber($company . $delimiter . $customer->getAccountNumber());
        }


        $accountCode = $this->getVarienData('customer_account_code', $customer);
        $name = $this->getVarienData('customer_account_name', $customer);
        $this->setMessageSubject($accountCode);
        $this->setMessageSecondarySubject($name);

        $erpAccount = $helper->processCustomerAction($customer, $this->getConfigBase());
        $this->setCusErpAccount($erpAccount);
    }

}
