<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Productcustomerskudelete extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    /**
     * @var \Epicor\Comm\Model\Customer\SkuFactory
     */
    protected $commCustomerSkuFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;


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
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory $accessroleErpAccountFactory)
    {
        $this->commCustomerSkuFactory = $commCustomerSkuFactory;
        $this->jsonHelper  = $jsonHelper;
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

        $response = array();
        $response['type'] = 'success-msg';
        $response['message'] = __('SKU was successfully deleted.');

        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->commCustomerSkuFactory->create()->load($id);
            /* @var $model Epicor_Comm_Model_Customer_Sku */

            try {

                //$model->load($id);
                
                if (!$id || !$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('No data found to delete'));
                }

                 $model->delete();
                 $this->messageManager->addSuccess( $response['message']);
            } catch (\Exception $e) {
                $response['type'] = 'error-msg';
                $response['message'] = $e->getMessage();
                $this->messageManager->addError( $response['message']);
            }
        } else {
            $response['type'] = 'error-msg';
            $response['message'] = __('No data found to delete');
            $this->messageManager->addError( $response['message']);
        }

        //M1 > M2 Translation Begin (Rule p2-7)
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        //$this->getResponse()->setBody($this->jsonHelper->jsonEncode($response));
        $this->_redirect('catalog/product/edit', array('id' => $this->getRequest()->getParam('productId')));
        return;
       
        //M1 > M2 Translation End
    }

    }
