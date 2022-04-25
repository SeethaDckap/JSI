<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class SyncRelatedDocs extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
{ 

    /**
     * @var \Epicor\Comm\Helper\Product\Relateddocuments\Sync
     */
    protected $commProductRelatedDocSyncHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,    
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Epicor\Comm\Helper\Product\Relateddocuments\Sync   $commProductRelatedDocSyncHelper
       )
    {   
        $this->commProductRelatedDocSyncHelper = $commProductRelatedDocSyncHelper;
        $this->response = $context->getResponse();
        parent::__construct($context, $backendAuthSession, $commCustomerSkuFactory);
    }
   
    
    public function execute()
    { 
        $productId = $this->getRequest()->getParam('product', null); 
        $helper = $this->commProductRelatedDocSyncHelper;
        /* @var $helper \Epicor\Comm\Helper\Product\Relateddocuments\Sync */

        $helper->processRelatedDocuments($productId, true); 
        $this->response->setBody('true');
    }
}
