<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Block\Html\Link;

/**
 * To Display Esdm - My Saved Cards conditionally
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
    

    private $tokenRequestData;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Esdm\Helper\ClientTokenData $clientTokenData,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        array $data = []
    ) {
        $this->tokenRequestData = $clientTokenData;
        $this->_defaultPath = $defaultPath;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $defaultPath, $data);
    }       
    
    /**
     * ECC1 method was addLinkToParentBlock
     * @return boolean
     */
    public function addLinkConitionally()
    {
        //$parent = $this->getParentBlock();
        $customer = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
        $show = true;
        $eccAccountType = $customer->getEccErpAccountType();

        $checkPaymentGatewayEnabled = $this->tokenRequestData->getConfigValue('active');
        if ($checkPaymentGatewayEnabled) {
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
