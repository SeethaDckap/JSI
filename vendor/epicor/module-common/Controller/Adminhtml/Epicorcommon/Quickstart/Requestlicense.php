<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart;

class Requestlicense extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $backendAuthSession
        );
    }

    public function execute()
    {
        $url = $this->getRequest()->getParam('url');
        $user = $this->getRequest()->getParam('user');
        $pass = $this->getRequest()->getParam('pass');

        $helper = $this->commMessagingHelper;
        $result = $helper->requestLicense($url, $user, $pass);

        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody($result);
        $this->_response->setBody($result);
        //M1 > M2 Translation End
    }

    }
