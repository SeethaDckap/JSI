<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message;


abstract class Ajax extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    
    /**
     * @var \Epicor\Comm\Model\Customer\SkuFactory
     */
    protected $commCustomerSkuFactory;
    
       public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory
        )
    {
         $this->commCustomerSkuFactory = $commCustomerSkuFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /*
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)

    {
        $this->commCustomerSkuFactory = $commCustomerSkuFactory;
        parent::__construct($context, $backendAuthSession);
    }
    */
    
    private function deleteCpn()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commCustomerSkuFactory->create()->load($id);
        if ($model->getId()) {
            try {
                $sku = $model->getSku();
                $model->delete();
                $this->messageManager->addSuccessMessage("Customer SKU : $sku deleted.");
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Failed to delete Customer SKU');
            }
        }
    }
}
