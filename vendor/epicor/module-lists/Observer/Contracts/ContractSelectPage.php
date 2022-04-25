<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Contracts;

class ContractSelectPage extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{   
    /**
     * Any list related actions required on page
     *
     * checks to see if any selected list is still valid
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Lists_Model_Observer_Customer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $countError = 0;
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        if ($helper->contractsDisabled()) {
            return $this;
        }

        $event = $observer->getEvent();
        /* @var $event Varien_Event */
        //M1 > M2 Translation Begin (Rule p2-6.2)
        //$controller = Mage::app()->getFrontController()->getAction();
        $fullActionName = $observer->getController();
        //M1 > M2 Translation End

        $cart = $this->checkoutCartHelper->getCart();
        /* @var $cart Epicor_Comm_Model_Cart */

        $stopCheckout = $helper->stopCheckout();
        if ( $stopCheckout &&
                           //$event->getControllerAction()->getRequest()->getPost() == false &&

            stripos($observer->getFullActionName(), 'epicor_lists_cart_contractselectgrid') === false &&
            stripos($observer->getFullActionName(), 'epicor_lists_cart_applycontractselect') === false &&
            stripos($observer->getFullActionName(), 'banner_ajax_load') === false) {
            if ($stopCheckout === true) {
                $required = $helper->requiredContractType();

                if ($required == 'H') {
                    $error = __('Checkout is disabled until you have selected a Contract');
                } else {
                    $error = __('Checkout is disabled until you have selected a Contract or all lines in the cart have been assigned a Contract');
                }
            } else {
                $error = $stopCheckout;
            }
            
                $messages = $this->messageManager->getMessages()->getErrors();
                foreach($messages as $message){
                    if($error->getText() === $message->getText()){
                        $countError++;
                    }
                }
            if ( stripos($observer->getRouteName(), 'checkout') === false &&
                stripos($observer->getFullActionName(), 'comm_cart_add') === false  &&
                stripos($observer->getFullActionName(), 'comm_quickadd_autocomplete') === false &&
                    stripos($fullActionName, 'section') === false  && $countError === 0)
            {
                 $this->messageManager->addErrorMessage($error);
            }
            if(stripos($observer->getFullActionName(), 'checkout_cart_add') === false &&
                stripos($observer->getFullActionName(), 'comm_quickadd_add') === false  &&
                stripos($observer->getFullActionName(), 'comm_cart_add') === false  &&
                stripos($observer->getFullActionName(), 'checkout_cart_updateitemoptions') === false ){ 
               $cart->getQuote()->addErrorInfo('error', 'epicor_lists', 'select_contract', $error);
            }
        }

        $selectedListId = $helper->getSelectedContract();

        if ($selectedListId) {

            $list = $this->listsListModelFactory->create()->load($selectedListId);
            /* @var $list Epicor_Lists_Model_ListModel */

            if ($list->isActive() == false) {
                $helper->selectContract(null);
                $this->contractSelect();
                return $this;
            }

            $valid = $helper->isValidContractId($selectedListId);

            if ($valid == false) {
                $helper->selectContract(null);
                $this->contractSelect();
                return $this;
            }
        }
    }

}