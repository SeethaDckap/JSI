<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class Networktest extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->response = $context->getResponse();
        parent::__construct($context, $backendAuthSession, $commCustomerSkuFactory );
    }

    public function execute()
    {

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $result = $helper->connectionTest();

        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody($result);
        $this->response->setBody($result);
        //M1 > M2 Translation End
    }

}
