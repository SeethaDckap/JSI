<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class OrphanCheck extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
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
        $dealerGrp = $this->loadEntity();

        $response = array(
            'message' => '',
            'type' => 'no_change',
            'erpaccounts' => 0,
            'exlusionerror' => false,
        );

        if ($dealerGrp->getId()) {
            $this->processERPAccountsSave($dealerGrp, $data);
            $response['erpaccounts'] = count($dealerGrp->getErpAccountsWithChanges());
            $response['exlusionerror'] = ($response['erpaccounts'] == 0);
        }

        $this->getResponse()->setBody(json_encode($response));
        
    }

}
