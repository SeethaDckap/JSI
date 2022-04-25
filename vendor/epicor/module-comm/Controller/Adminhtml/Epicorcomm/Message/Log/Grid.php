<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log;

class Grid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log
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

        $source = $this->getRequest()->getParam('source');
        $sourceId = $this->getSourceParams();
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('epicor_comm/adminhtml_catalog_product_edit_tab_log')
            ->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }

    }
