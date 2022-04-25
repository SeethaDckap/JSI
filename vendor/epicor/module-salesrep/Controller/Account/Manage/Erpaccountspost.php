<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Erpaccountspost extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    public function __construct(
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\SalesRep\Controller\Context $context)
    {
        $this->backendJsHelper = $backendJsHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        $erpAccounts = $this->getRequest()->getParam('selected_erpaccounts');
        if ($data = $this->getRequest()->getParams()) {
            if (!is_null($erpAccounts)) {
                $salesReps = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
                // load current and check if any need to be removed

                $helper = $this->salesRepAccountManageHelper;
                /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

                $salesRepAccount = $helper->getManagedSalesRepAccount();
                $salesRepAccount->setErpAccounts($salesReps);
                $salesRepAccount->save();
                $this->messageManager->addSuccessMessage(__('Sales Rep Account Updated Successfully'));
            }
        }

        $this->_redirect('*/*/erpaccounts');
    }

    }
