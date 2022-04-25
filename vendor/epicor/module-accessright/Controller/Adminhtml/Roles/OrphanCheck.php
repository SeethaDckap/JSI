<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class OrphanCheck extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * OrphanCheck constructor.
     * Validate ERP Accounts and Customer selection before save.
     *
     * @param \Epicor\AccessRight\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Retrieve the erp account and customer details before save
     *
     * @return string
     */
    public function execute()
    {
        $data = $this->request->getParams();
        $role = $this->loadEntity();

        $response = array(
            'message' => '',
            'type' => 'no_change',
            'erpaccounts' => 0,
            'exlusionerror' => false,
        );

        //if ($role->getId()) {
        $this->processERPAccountsSave($role, $data);
        $this->processCustomersSave($role, $data);
        $warning = $role->orphanCheck('warn');

        if ($warning) {
            $response = array_merge($response, $warning);
        } else {
            $response['erpaccounts'] = count($role->getErpAccountsWithChanges());
        }

        $inclusion = $role->getErpAccountsExclusion() == 'N';
        $validLinkType = $role->getErpAccountLinkType() != 'N';

        $response['exlusionerror'] = ($inclusion && $validLinkType && $response['erpaccounts'] == 0);
        // }

        $this->getResponse()->setBody(json_encode($response));

    }
}
