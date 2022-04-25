<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log;

class Index extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log
{

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        parent::__construct($context, $commMessageLogFactory, $commMessagingHelper, $backendAuthSession);
    }


    public function execute()
    {
        return $this->_initPage();
    }

    }
