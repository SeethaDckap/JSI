<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Customer;


/**
 * Customer Address Helper
 *
 * @author Gareth.James
 */
class Address extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;


	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSessionFactory,
        \Epicor\Common\Helper\ContextFactory $commHelperFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory
    ) {
        
        $this->storeManager = $storeManager;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;      
        $this->customerSession = $customerSessionFactory;
        $this->commHelperFactory = $commHelperFactory;
        parent::__construct($context);
    }
	
    /**
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param type $type
     * @param type $customer
     * @return type
     */
    public function getCustomerDefaultAddress($erpAccount, $type, $customer = null)
    {
	
	// load addresses for current store and find default for type
        if (empty($customer)) {
            $customer = $this->customerSession->getCustomer();
        }

        $store = $this->storeManager->getStore()->getId();

        $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();

        $code = ($type == 'delivery') ? $erpAccount->getDefaultDeliveryAddressCode() : $erpAccount->getDefaultInvoiceAddressCode();

        $collection->addFieldToFilter('erp_customer_group_code', $this->getErpAccountNumber($erpAccount->getId(),$store));
        $collection->addFieldToFilter('erp_code', $code);
        $collection->getSelect()->join(array('stores' => $collection->getTable('ecc_erp_account_address_store')), 'main_table.entity_id = stores.erp_customer_group_address', array('stores.erp_customer_group_address'), null);
        $collection->addFieldToFilter('stores.store', $store);

        return $collection->getFirstItem();
    }

    public function getCustomerAddresses($customer = null, $type = '')
    {

	 // load all for current store & return collection
        if (empty($customer)) {
            $customer = $this->customerSession->getCustomer();
        }

        $store = $this->storeManager->getStore()->getId();

        $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\Collection */

        $collection->addFieldToFilter('erp_customer_group_code', $this->getErpAccountNumber());
        $collection->getSelect()->join(array('stores' => $collection->getTable('ecc_erp_account_address_store')), 'main_table.entity_id = stores.erp_customer_group_address', array('stores.erp_customer_group_address'), null);
        $collection->addFieldToFilter('stores.store', $store);
        if (!empty($type)) {
            $collection->addFieldToFilter('is_' . $type, array('eq' => '1'));
        }
        return $collection->getItems();
    }
    
    public function isMasquerading()
    {
	$customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */
        $masquerade = $customerSession->getMasqueradeAccountId();

        return !empty($masquerade);
    }

	
    public function getErpAccountNumber($erpAccountId = null, $storeId = null)
    {

       return  $this->commHelperFactory->create()->getCommHelper()->getErpAccountNumber($erpAccountId, $storeId);
    }
	
    
    /**
     * Get Erp Account Information
     *
     * @param int $erpAccountId
     * @param string $type
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpAccountInfo(
        $erpAccountId = null,
        $type = 'customer',
        $storeId = null,
        $allowMasquerade = true
    ) {

      return  $this->commHelperFactory->create()->getCommHelper()->getErpAccountInfo($erpAccountId, $type, $storeId);
    }
    

}
