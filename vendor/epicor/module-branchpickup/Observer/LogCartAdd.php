<?php
namespace Epicor\BranchPickup\Observer;

class LogCartAdd extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Check the product is available for the pickup location(Adding the product to the cart)
     * 
     * @return message
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $selectedBranch = $this->_helper->getSelectedBranch();
        /* @var $event Varien_Event */
        $event = $observer->getEvent();
        /* @var $controller Epicor_Comm_CartController */
        $controller = $event->getControllerAction();
        $productId = $controller->getRequest()->getParam('product');
        if (empty($productId)) {
            $productId = $controller->getRequest()->getParam('product_id');
        }
        if (empty($productId)) {
            $productId = $controller->getRequest()->getParam('id');
        }
        $refererUrl = $this->getRefererUrl();
        if ((!empty($selectedBranch)) && (!empty($productId))) {
            $productModel = $this->_branchModel->checkProductAvailability($selectedBranch, $productId);
            /* @var model Epicor_BranchPickup_Model_BranchPickup */
            if (!$productModel) {
                $this->messageManager->addError('Product is not available for the pickup location');
                //M1 > M2 Translation Begin (Rule p2-3)
                /*Mage::app()->getResponse()->setRedirect($refererUrl);
                Mage::app()->getResponse()->sendResponse();*/
                $this->response->setRedirect($refererUrl);
                $this->response->sendResponse();
                //M1 > M2 Translation End
                exit;
            }
        }
    }

}