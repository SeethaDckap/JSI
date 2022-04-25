<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Contract;

class Select extends \Epicor\Lists\Controller\Contract
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Helper\Session $listsSessionHelper
    )
    {
        $this->checkoutCart = $checkoutCart;
        $this->registry = $registry;
        $this->listsSessionHelper = $listsSessionHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $listsFrontendContractHelper,
            $listsListModelFactory
        );
    }

    /**
     * Contract Select Page
     *
     */
    public function execute()
    {
        $contractHelper = $this->listsFrontendContractHelper;
        if ($contractHelper->contractsDisabled()) {
            //M1 > M2 Translation Begin (Rule p2-6.2)
            //Mage::app()->getFrontController()->getResponse()->setRedirect($this->_url->getBaseUrl());
            return $this->_redirect($this->_url->getBaseUrl());
            //M1 > M2 Translation End
        }
        $quote = $this->checkoutCart->getQuote();
        $this->registry->register('ecc_checkout_has_items', $quote->hasItems());
        $sessionHelper = $this->listsSessionHelper;
        if ($sessionHelper->getValue('ecc_optional_select_contract_show')) {
            $sessionHelper->setValue('ecc_optional_select_contract_show', false);
        }
        
        $result = $this->resultPageFactory->create();

        return $result;
    }

}
