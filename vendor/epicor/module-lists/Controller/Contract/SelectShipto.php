<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class SelectShipto extends \Epicor\Lists\Controller\Contract
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    
        public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
                \Magento\Framework\Registry $registry

    )
    {

        $this->registry = $registry;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
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
     * Contract Select Action
     */
    public function execute()
    {
        $this->registry->unregister('ecc_contract_allow_change_shipto');
        $this->registry->register('ecc_contract_allow_change_shipto', true);

        $shipto = $this->getRequest()->getParam('shipto');

        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        if ($shipto && $helper->isValidShiptoAddressCode($shipto)) {
            $helper->selectContractShipto($shipto);
        }

        if ($shipto == -1) {
            $helper->selectContractShipto(false);
        }
        
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {
            $returnUrl = $helper->urlDecode($returnUrl);
            $this->_redirectUrl($returnUrl);
        } else {
            $this->_redirect('/');
        }
    }

}
