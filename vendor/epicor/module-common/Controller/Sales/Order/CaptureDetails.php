<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Sales\Order;

class CaptureDetails extends \Epicor\Common\Controller\Sales\Order
{

    /**
     * Action for reorder
     */
    public function execute()
    {
        $productSkusJSON = $this->getRequest()->getParam('productSkus');
        $registerAccount = $this->getRequest()->getParam('registerAccount');
        $source = $this->getRequest()->getParam('source');
        if ($productSkusJSON) {
            $this->_coreRegistry->unregister('rfq_product_skus');
            $this->_coreRegistry->register('rfq_product_skus', $productSkusJSON);
        }
        $data = $this->getRequest()->getParam('data');

        //don't attempt to register if customer is logged in 
        if ($registerAccount && !$this->customerSession->isLoggedIn()) {
             /* @var $helper Epicor_Common_Helper_Data */
            $this->commonData->saveCustomerDetails($data);
        }
        $this->commonData->retrieveNonErpProductsInCart($data, false, $source);
        $this->getResponse()->setBody(json_encode(array('success' => true)));
    }

    }
