<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer;


    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * Description of SalesrepController
 *
 * @author Paul.Ketelle
 */
abstract class Salesrep extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context
    )
    {
        $this->backendAuthSession = $context->getBackendAuthSession();
        $this->backendJsHelper = $context->getBackendJsHelper();
        $this->customerResourceModelCustomerCollectionFactory = $context->getCustomerResourceModelCustomerCollectionFactory();
        $this->customerCustomerFactory = $context->getCustomerCustomerFactory();
        $this->salesRepAccountFactory = $context->getSalesRepAccountFactory();
        $this->registry = $context->getRegistry();
        parent::__construct(
            $context
        );
    }


    protected function _isAllowed()
    {
        return $this->backendAuthSession->isAllowed('Epicor_SalesRep::customer_salesrep');
    }

    protected function _processDetailsSave(&$salesRep, $data)
    {
        if (isset($data['sales_rep_name'])) {
            $salesRep->setName($data['sales_rep_name']);
        }
        if (isset($data['sales_rep_id'])) {
            $salesRep->setSalesRepId($data['sales_rep_id']);
        }
        if (isset($data['catalog_access'])) {
            $allowed = !empty($data['catalog_access']) ? $data['catalog_access'] : false;
            $salesRep->setCatalogAccess($allowed);
        }
    }

    protected function _processHierarchySave(&$salesRep, $data)
    {
        $parents = $this->getRequest()->getParam('selected_parents', false);
        if ($parents !== false) {
            $this->_saveParentAccounts($salesRep, $data);
        }

        $children = $this->getRequest()->getParam('selected_children', false);
        if ($children !== false) {
            $this->_saveChildAccounts($salesRep, $data);
        }
    }

    /**
     *
     * @param \Epicor\SalesRep\Model\Account $salesRep
     * @param type $data
     */
    protected function _saveParentAccounts(&$salesRep, $data)
    {
        $parents = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['parents']));
        // load current and check if any need to be removed

        $salesRep->setParentAccounts($parents);
    }

    /**
     *
     * @param \Epicor\SalesRep\Model\Account $salesRep
     * @param type $data
     */
    protected function _saveChildAccounts(&$salesRep, $data)
    {
        $children = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['children']));
        // load current and check if any need to be removed

        $salesRep->setChildAccounts($children);
    }

    protected function _processSalesRepsSave(&$salesRep, $data)
    {
        $salesReps = $this->getRequest()->getParam('selected_salesreps');
        if (!is_null($salesReps)) {
            $this->_saveSalesReps($salesRep, $data);
        }
    }

    protected function _saveSalesReps(&$salesRep, $data)
    {
        if(isset($data['links']['salesreps'])){
            $salesReps = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['salesreps']));

            // load current and check if any need to be removed

            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addFieldToFilter('ecc_sales_rep_account_id', $salesRep->getId());

            $existing = array();
            /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
            foreach ($collection->getItems() as $customer) {
                if (!in_array($customer->getId(), $salesReps)) {
                    $customer->setEccSalesRepAccountId(false);
                    $customer->save();
                } else {
                    $existing[] = $customer->getId();
                }
            }

            // loop through passed values and only update customers who are new
            foreach ($salesReps as $customerId) {
                if (!in_array($customerId, $existing)) {
                    $customerModel = $this->customerCustomerFactory->create()->load($customerId);
                    if (!$customerModel->isObjectNew()) {
                        $customerModel->setEccSalesRepAccountId($salesRep->getId());
                        $customerModel->save();
                    }
                }
            }
        }
    }

    protected function _processPricingRulesSave(&$salesRep, $data)
    {
        $salesRep->setName($data['name']);
        $salesRep->setSalesRepId($data['sales_rep_id']);
    }

    protected function _processErpAccountsSave(&$salesRep, $data)
    {
        $erpAccounts = $this->getRequest()->getParam('selected_erpaccounts');
        if (!is_null($erpAccounts)) {
            $this->_saveErpAccounts($salesRep, $data);
        }
    }

    /**
     *
     * @param \Epicor\SalesRep\Model\Account $salesRep
     * @param type $data
     */
    protected function _saveErpAccounts($salesRep, $data)
    {
        $salesReps = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
        // load current and check if any need to be removed

        $salesRep->setErpAccounts($salesReps);
    }

    /**
     *
     * @return \Epicor\SalesRep\Model\Account
     */
    protected function _initSalesRepAccount()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->salesRepAccountFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            }
        }

        $this->registry->register('salesrep_account', $model);

        return $model;
    }

    protected function delete($id, $mass = false)
    {
        try {
            $model = $this->salesRepAccountFactory->create()->load($id);
            if ($model->delete()) {
                if (!$mass) {
                    $this->messageManager->addSuccessMessage(__('The Sales Rep Account has been deleted.'));
                }
            } else {
                $this->messageManager->addErrorMessage('Could not delete Sales Rep Account ' . $model->getErpCode());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
