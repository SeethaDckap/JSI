<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Create extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\CncFactory
     */
    protected $commMessageRequestCncFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

   public function __construct(
       \Epicor\Comm\Controller\Adminhtml\Context $context,
       \Magento\Backend\Model\Auth\Session $backendAuthSession,
       \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
       \Magento\Backend\Helper\Js $backendJsHelper,
       \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
       \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
       \Epicor\Comm\Helper\Data $commHelper,
       \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
       \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory $commResourceCustomerErpaccountStoreCollectionFactory,
       \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory $commCustomerErpaccountStoreFactory,
       \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory,
       \Epicor\SalesRep\Model\ErpaccountFactory $salesRepErpaccountFactory,
       \Epicor\Comm\Model\Message\Request\CncFactory $commMessageRequestCncFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Common\Helper\Data $commonHelper,
       \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory $accessroleErpAccountFactory)
   {
       $this->commMessageRequestCncFactory = $commMessageRequestCncFactory;
        $this->commMessagingHelper = $commMessagingHelper;
       parent::__construct(
           $context,
           $backendAuthSession,
           $commCustomerErpaccountFactory,
           $backendJsHelper,
           $customerResourceModelCustomerCollectionFactory,
           $customerCustomerFactory,
           $commHelper,
           $scopeConfig,
           $commResourceCustomerErpaccountStoreCollectionFactory,
           $commCustomerErpaccountStoreFactory,
           $salesRepResourceErpaccountCollectionFactory,
           $salesRepErpaccountFactory,
           $commonHelper,
           $resourceConfig,
           $accessroleErpAccountFactory);
   }

    public function execute()
    {
        $error = null;
        if ($data = $this->getRequest()->getPost()) {
            try {
                $cnc = $this->commMessageRequestCncFactory->create();

                if ($cnc->isActive()) {

                    $helper = $this->commMessagingHelper;

                    if (strpos($data['store'], 'store_') !== false) {
                        $storeId = str_replace('store_', '', $data['store']);
                        $brand = $helper->getStoreBranding($storeId);
                    } else {
                        $webId = str_replace('website_', '', $data['store']);
                        $brand = $helper->getWebsiteBranding($webId);
                    }

                    $cnc->setBranding($brand);

                    $erpAccount = $this->commCustomerErpaccountFactory->create();
                    $erpAccount->setName($data['name']);
                    $erpAccount->setEmail($data['email']);
                    $erpAccount->setAccountType($data['account_type']);

                    $erpAccount->addAddress('registered', 'registered', $data['registered']);
                    $erpAccount->addAddress('delivery', 'delivery', $data['delivery']);
                    $erpAccount->addAddress('invoice', 'invoice', $data['invoice']);

                    $cnc->setAccount($erpAccount);
                    if ($cnc->sendMessage()) {
                        $this->messageManager->addSuccessMessage(__('ERP Account created'));
                        $this->backendSession->setFormData(false);
                    } else {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$error = $this->__('ERP Account creation failed. Error - %s', $cnc->getStatusDescriptionText());
                        $error = __('ERP Account creation failed. Error - %1', $cnc->getStatusDescriptionText());
                        //M1 > M2 Translation End
                    }
                } else {
                    $error = __('ERP Account creation failed. CNC Message not Active');
                }
            } catch (\Exception $e) {
                //M1 > M2 Translation Begin (Rule 55)
                //$error = $this->__('ERP Account creation failed. Error  - %s', $e->getMessage());
                $error = __('ERP Account creation failed. Error  - %1', $e->getMessage());
                //M1 > M2 Translation End
            }
        } else {
            $error = __('No data found to save');
        }

        if ($error) {
            $this->_getSession()->setFormData($data);
            $this->messageManager->addErrorMessage($error);
            $this->_redirect('*/*/new');
        } else {
            $this->_redirect('*/*/index');
        }
    }

    }
