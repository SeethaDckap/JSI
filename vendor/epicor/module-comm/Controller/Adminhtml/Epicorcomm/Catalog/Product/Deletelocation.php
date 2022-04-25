<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Catalog\Product;

class Deletelocation extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Catalog\Product
{

    /**
     * @var \Epicor\Comm\Model\Location\ProductFactory
     */
    protected $commLocationProductFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Location\ProductFactory $commLocationProductFactory
    ) {
        $this->commLocationProductFactory = $commLocationProductFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $response['message'] = __('Location was successfully deleted.');
        $locationId = $this->getRequest()->getParam('id');
        try {
            $locationProduct = $this->commLocationProductFactory->create()->load($locationId);
            $locationProduct->delete();
            echo $locationProduct->getLocationCode();
          //  $this->messageManager->addSuccess( $response['message']);
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $this->messageManager->addError( $response['message']);
            $this->_redirect('catalog/product/edit', array('id' => $this->getRequest()->getParam('productId')));
            return;
        }
    //    $this->_redirect('catalog/product/edit', array('id' => $this->getRequest()->getParam('productId')));
      //  return;
    }

    }
