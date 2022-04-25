<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Contracts;

class ContractSelectionObserver extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{   
//      /**
//  * @var EventManager
//  */
//  protected $eventManager;
//
//  public function __construct(\Magento\Framework\Event\Manager $eventManager){
//    $this->eventManager = $eventManager;
//  }
    /**
     * Checks lists to see if any need selecting on login and decides the type of contract selection
     */
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper \Epicor\Lists\Helper\Frontend\Contract */

        if ($helper->contractsDisabled()) {
            return $this;
        }

        $event = $observer->getEvent();
        /* @var $event Varien_Event */
        $ajax = $event->getControllerAction()->getRequest()->getParam('ajax');

        //M1 > M2 Translation Begin (Rule p2-6.2)
        //$controller = Mage::app()->getFrontController()->getAction();
        $controller = $this->request;
        //M1 > M2 Translation End

        $commData = stripos($controller->getFullActionName(), 'epicor_comm_data') !== false;
        $logout = stripos($controller->getFullActionName(), 'logout') !== false;
        $loginPost = stripos($controller->getFullActionName(), 'loginpost') !== false;
        $store = stripos($controller->getFullActionName(), 'comm_store_select') !== false;
        $arpayments = stripos($controller->getFullActionName(), 'customerconnect_arpayments_archeckout') !== false;

        if ($ajax || $commData || $logout || $loginPost || $store || $arpayments) {
            return $this;
        }

        $contracts = stripos($controller->getFullActionName(), 'epicor_lists_contract') !== false;

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper \Epicor\Lists\Helper\Session */

        $sessionHelper->storeCheck();

        if ($contracts) {
            return $this;
        }

        $selectedListId = $helper->getSelectedContract();

        if ($sessionHelper->getValue('ecc_contract_selection_started') || $selectedListId) {
            $observerArray = $observer->toArray();
            array_push($observerArray, $controller->getFullActionName());
            $observerArray['route_name']= $controller->getRouteName();
            $observerArray['full_action_name']= $controller->getFullActionName();
            $observerArray['controller_name']= $controller->getControllerName();
            $observerArray['action_name']= $controller->getActionName();
            return $this->eventManager->dispatch('epicor_contract_select_page',$observerArray);
        } else {
            return $this->eventManager->dispatch('epicor_contract_select_login');
        }
    }

}