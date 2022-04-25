<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Observer\Contracts;

class ContractsCartCheck extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     *
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     *
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    /**
     *
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     *
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @var \Epicor\Lists\Helper\Frontend\ProductFactory
     */
    protected $listsFrontendProductHelper;

    /**
     *
     * @var \Magento\Framework\DataObjectFactory
     */
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
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Lists\Helper\Frontend\ProductFactory $listsFrontendProductHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->customerSession = $customerSession;
        parent::__construct(
            $registry,
            $listsFrontendContractHelper,
            $listsSessionHelper,
            $checkoutCartHelper,
            $listsListModelFactory,
            $eventManager,
            $listsFrontendProductHelper,
            $dataObjectFactory,
            $request,
            $messageManager
        );
    }

    /**
     * Validates cart items when user logges in or selects/changes contract
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($contractHelper->contractsDisabled()) {
            return $this;
        }

        $helper = $this->listsFrontendProductHelper->create();
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */

        $code = $contractHelper->getSelectedContractCode();
        if (empty($code)) {
            $contractHelper->resetLinesCheckExistingProducts();
        }

        $this->setContractCodeToItems();

        $cart = $this->checkoutCartHelper->getCart();
        /* @var $cart Epicor_Comm_Model_Cart */
        $collectTotals = false;
        $erpAccount = $contractHelper->getErpAccountInfo();
        foreach ($cart->getItems() as $item) {
            $productId = $item->getProduct()->getId();

            $isValid = $helper->productIsValidForCart($productId, $item->getEccContractCode());
            if ($isValid === false) {
                $collectTotals = true;
                $item->delete();
                $error = __('Product %1 was removed from cart, not valid for selected contract.', $item->getProduct()->getSku());
                if ($helper->errorExists($error) == false) {
                    $this->messageManager->addErrorMessage($error);
                }

            }
            // this method is only called for a header line contract and so line level contracts should be null
            if ($erpAccount->getRequiredContractType() == 'H' || $erpAccount->getAllowedContractType() == 'H') {
                $item->setEccContractCode(null);
            }
        }
        if ($collectTotals === true) {
            $cart->getQuote()->setTotalsCollectedFlag(false);
        }
        $cart->save();

        $this->contractCartMergeOnLogin($contractHelper, $cart);
    }

    /**
     * Auto Select Contract if it has a header contract and no contract would be auto-selected,
     *
     */
    public function contractCartMergeOnLogin($contractHelper, $cart)
    {
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            //we’ll check the cart on login, if it has a header contract and no contract would be auto-selected,
            //then it will “select” the contract that was previously selected so it’s obvious that the data linkage is there.
            $quotes = $cart->getQuote();
            $getSelectedEccContract = $quotes->getEccContractCode();
            if (($getSelectedEccContract) && (!$contractHelper->contractsDisabled())) {
                $contractId = $contractHelper->retrieveContractId($getSelectedEccContract);
                $validContract = $contractHelper->isValidContractId($contractId);
                if (($contractId) && ($validContract) && $cart->getItemsQty() > 0) {
                    $sessionHelper = $this->listsSessionHelper;
                    /* @var $sessionHelper \Epicor\Lists\Helper\Session */
                    $sessionHelper->setValue('ecc_selected_contract', $contractId);
                    $sessionHelper->setValue('ecc_contract_checkout_disabled', false);
                    $contractHelper->setAddressForContract($contractId);
                }
            }
        }
    }


    /**
     * ECC-9170  Multi msq and bsv in section data when contract is applied
     *
     *
     */
    protected function setContractCodeToItems()
    {
        $helper = $this->listsFrontendContractHelper;
        $selectedListCode = $helper->getSelectedContractCode();
        $cart = $this->checkoutCartHelper->getCart();
        $totalItemsInCart = $this->checkoutCartHelper->getItemsCount();
        if (($totalItemsInCart) && ($selectedListCode)) {
            foreach ($cart->getItems() as $item) {
                $item->setEccContractCode($selectedListCode);
                $item->save();
            }
            $cart->getQuote()->setTotalsCollectedFlag(true)->save();
        }
    }

}
