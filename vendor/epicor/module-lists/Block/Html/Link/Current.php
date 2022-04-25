<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Html\Link;

/**
 * To Display Manage Lists conditionally
 */
class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Default path
     *
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_defaultPath;

    protected $customerSession;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;
    
    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;    

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_defaultPath = $defaultPath;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerSession = $customerSession;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
    }        
    
    /**
     * ECC1 method was addLinkToParentBlock
     * @return boolean
     */
    public function addLinkConitionally()
    {
        //$parent = $this->getParentBlock();
        $customer = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
        $contractHelper = $this->listsFrontendContractHelper;
        $show = true;
        $eccAccountType = $customer->getEccErpAccountType();
        //Manage Lists should not be available on the Supplier's "My Account" Menu
        if ($eccAccountType == "supplier") {
            $show = false;
        }
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        //&& $contractHelper->contractsEnabled()
        if ($contractHelper->listsEnabled() && $show) {

            return true;
        } else {

            return false;
        }
    }    
    
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->addLinkConitionally()) {

            return parent::_toHtml();
        } else {

            return false;
        }
    } 
}
