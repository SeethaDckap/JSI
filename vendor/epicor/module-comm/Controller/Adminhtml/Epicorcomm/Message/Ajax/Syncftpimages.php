<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class Syncftpimages extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
{ 

    /**
     * @var \Epicor\Comm\Helper\Product\Image\Sync
     */
    protected $commProductImageSyncHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,    
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
         \Epicor\Comm\Helper\Product\Image\Sync   $commProductImageSyncHelper
       )
    {   
        $this->commProductImageSyncHelper = $commProductImageSyncHelper;
        $this->response = $context->getResponse();
        parent::__construct($context, $backendAuthSession, $commCustomerSkuFactory);
    }
   
    
    public function execute()
    { 
        $productId = $this->getRequest()->getParam('product', null); 
        $helper = $this->commProductImageSyncHelper;
        /* @var $helper Epicor_Comm_Helper_Product */
        $helper->processErpImages($productId, true); 
        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody('true');
        $this->response->setBody('true');
        //M1 > M2 Translation End
    }
}
