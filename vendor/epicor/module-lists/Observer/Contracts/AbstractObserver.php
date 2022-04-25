<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Contracts;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $registry;

    protected $listsFrontendContractHelper;

    protected $listsSessionHelper;

    protected $checkoutCartHelper;

    //protected $generic;

    protected $listsListModelFactory;

    protected $eventManager;

    protected $listsFrontendProductHelper;

    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    protected $messageManager;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
       // \Magento\Framework\Session\Generic $generic,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Lists\Helper\Frontend\ProductFactory $listsFrontendProductHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->checkoutCartHelper = $checkoutCartHelper;
       // $this->generic = $generic;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->eventManager = $eventManager;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }

    
    protected function setContractCodeToItems() {
        
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        $selectedListCode = $helper->getSelectedContractCode();
        $cart = $this->checkoutCartHelper->getCart();
        /* @var $cart Epicor_Comm_Model_Cart */
        $totalItemsInCart = $this->checkoutCartHelper->getItemsCount();
        if (($totalItemsInCart) && ($selectedListCode)) {
            foreach ($cart->getItems() as $item) {
                $item->setEccContractCode($selectedListCode);
                $item->save();
            }
            $cart->save();
        }
    }
    
     /**
     * Checks lists to see if any need selecting on login
     */
    public function contractSelectLogin(\Magento\Framework\Event\Observer $observer)
    {
       $this->registry->unregister('ecc_contract_allow_change_shipto');
        $this->registry->register('ecc_contract_allow_change_shipto', true);
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        if ($helper->contractsDisabled()) {
            return $this;
        }
        $this->contractSelect($observer);
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_contract_selection_started', true);
        return $this;
    }
    
     /**
     * Checks active lists, and whether one can be selected
     */
    public function contractSelect(\Magento\Framework\Event\Observer $observer = null)
    {
       $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setSkipLists(false);
        $this->eventManager->dispatch('epicor_lists_login_check_before', array('transport' => $transportObject));
        $skipLists = $transportObject->getSkipLists();

        if ($skipLists) {
            return;
        }

        $activeContracts = $helper->getActiveContracts();
        if (empty($activeContracts)) {
            return;
        }

        $helper->autoSelectContract();

        $this->eventManager->dispatch('epicor_lists_login_check_after', array('transport' => $transportObject));
    }
    
    /**
     * Any list related actions required on page
     *
     * checks to see if any selected list is still valid
     *
     * @param Varien_Event_Observer $observer
     * @return \Epicor_Lists_Model_Observer_Customer
     */
    public function contractSelectPage(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        if ($helper->contractsDisabled()) {
            return $this;
        }

        $event = $observer->getEvent();
        /* @var $event Varien_Event */
        //M1 > M2 Translation Begin (Rule p2-6.2)
        //$controller = Mage::app()->getFrontController()->getAction();
        $controller = $this->request;
        //M1 > M2 Translation End

        $cart = $this->checkoutCartHelper->getCart();
        /* @var $cart Epicor_Comm_Model_Cart */

        $stopCheckout = $helper->stopCheckout();

        if (
            $stopCheckout &&
            $event->getControllerAction()->getRequest()->getPost() == false &&
            stripos($controller->getFullActionName(), 'selectcontract') === false &&
            stripos($controller->getFullActionName(), 'contractselect') === false
        ) {
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

            if (stripos($controller->getFullActionName(), 'checkout') === false &&
                $helper->errorExists($error) == false)
            {
                $this->messageManager->addErrorMessage($error);
            }

            $cart->getQuote()->addErrorInfo('error', 'epicor_lists', 'select_contract', $error);
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

