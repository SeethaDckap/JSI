<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class OrphanCheck extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    )
    {
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
        $list = $this->loadEntity();

        $response = array(
            'message' => '',
            'type' => 'no_change',
            'erpaccounts' => 0,
            'exlusionerror' => false,
        );

        if ($list->getId()) {
            $this->processERPAccountsSave($list, $data);
            $this->processCustomersSave($list, $data);
            $warning = $list->orphanCheck('warn');

            if ($warning) {
                $response = array_merge($response, $warning);
            } else {
                $response['erpaccounts'] = count($list->getErpAccountsWithChanges());
            }

            $inclusion = $list->getErpAccountsExclusion() == 'N';
            $validLinkType = $list->getErpAccountLinkType() != 'N';

            $response['exlusionerror'] = ($inclusion && $validLinkType && $response['erpaccounts'] == 0);
        }

        $this->getResponse()->setBody(json_encode($response));
        
    }

}
