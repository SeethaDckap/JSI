<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Customerskupost extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
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
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig)
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
            $resourceConfig);
    }

    public function execute()
    {

        $response = array();
        $response['type'] = 'success-msg';
        $response['message'] = __('SKU was successfully saved.');

        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->commCustomerSkuFactory->create();
            /* @var $model Epicor_Comm_Model_Customer_Sku */

            try {
                if ($id) {
                    $model->load($id);
                }

                $model->setProductId($this->getRequest()->getParam('product_id'));
                $model->setSku($this->getRequest()->getParam('sku'));
                $model->setDescription($this->getRequest()->getParam('description'));
                $model->setCustomerGroupId($this->getRequest()->getParam('customer_group_id'));

                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving SKU'));
                }
            } catch (\Exception $e) {
                $response['type'] = 'error-msg';
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['type'] = 'error-msg';
            $response['message'] = __('No data found to save');
        }

        //M1 > M2 Translation Begin (Rule p2-7)
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($response));
        //M1 > M2 Translation End
    }

    }
