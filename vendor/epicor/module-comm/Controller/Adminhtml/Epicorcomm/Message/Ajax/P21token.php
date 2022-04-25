<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class P21token extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
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
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper)
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->response = $context->getResponse();
        parent::__construct(
            $context,
            $backendAuthSession,
            $commCustomerSkuFactory);
    }

    /**
     * Gets the p21 token via curl
     */
    public function execute()
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody($helper->getP21Token());
        $this->response->setBody($helper->getP21Token());
        //M1 > M2 Translation End
    }

}
