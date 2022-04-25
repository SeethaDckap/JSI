<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Onepage;

class SaveContact extends \Epicor\SalesRep\Controller\Onepage
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;


    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->commHelper = $commHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
    }
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('salesrep_contact', false);

            $salesRepInfo = '';
            $salesRepCustomerId = '';

            if ($data) {
                $salesRepInfo = base64_decode($data);
                $salesRepData = unserialize($salesRepInfo);

                $helper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */

                $erpAccount = $helper->getErpAccountInfo();

                if (!empty($salesRepData['ecc_login_id'])) {
                    $collection = $this->customerResourceModelCustomerCollectionFactory->create();
                    $collection->addAttributeToFilter('contact_code', $salesRepData['contact_code']);
                    $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccount->getId());
                    $collection->addFieldToFilter('website_id', $this->storeManager->getStore()->getWebsiteId());
                    $customer = $collection->getFirstItem();
                    $salesRepCustomerId = $customer->getId();
                }
            }

            $customerSession = $this->customerSession;
            /* @var $customerSession Mage_Customer_Model_Session */

            $customer = $customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            $this->getOnepage()->getQuote()->setEccSalesrepCustomerId($customer->getId());
            $this->getOnepage()->getQuote()->setEccSalesrepChosenCustomerId($salesRepCustomerId);
            $this->getOnepage()->getQuote()->setEccSalesrepChosenCustomerInfo($salesRepInfo);
            $this->getOnepage()->getQuote()->collectTotals()->save();

            $result = array();
            $result['goto_section'] = 'billing';
            $result['update_section'] = array(
                array(
                    'name' => 'billing',
                    'html' => $this->_getBillingHtml()
                ),
                array(
                    'name' => 'shipping',
                    'html' => $this->_getShippingHtml()
                ),
            );

            $this->getOnepage()->getCheckout()
                ->setStepData('salesrep_contact', 'allow', true)
                ->setStepData('salesrep_contact', 'complete', true)
                ->setStepData('billing', 'allow', true);
            //M1 > M2 Translation Begin (Rule p2-7)
            //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
            //M1 > M2 Translation End
        }
    }

    }
