<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Controller\Index;


class Index extends \Magento\Checkout\Controller\Index\Index
{
    /**
     * Shipping method save action
     */
    public function execute()
    {
        if ($this->getRequest()->get('grid')) {

            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock('epicor_comm/customer_account_billingaddress_list')->toHtml()
            );
        }
        $helper = $this->_objectManager->get(\Epicor\AccessRight\Helper\Data::class)->getAccessAuthorization();
        if (!$helper->isAllowed('Epicor_Checkout::checkout_checkout_can_checkout')) {
            $this->messageManager->addErrorMessage($helper->getMessage());
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        return parent::execute();
    }
}