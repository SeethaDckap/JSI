<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout;
use Magento\Store\Model\ScopeInterface;

class Agreements extends \Magento\CheckoutAgreements\Block\Agreements
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;


    protected $request;

    /**
     * @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory
     */
    protected $_agreementCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->scopeConfig = $context->getScopeConfig();
        $this->checkoutSession = $checkoutSession;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->request = $context->getRequest();
        $this->_agreementCollectionFactory = $agreementCollectionFactory;
        parent::__construct(
            $context,
            $agreementCollectionFactory,
            $data
        );
    }

    public function checkPage()
    {
        $urls = $this->request->getFullActionName();
        if($urls = "paypal_express_review") {
            return false;
        }
        return true;

    }


    /**
     * Override block template
     *
     * @return string
     */
    protected function _toHtml()
    {
        if($this->checkPage()) {
            $this->setTemplate('epicor_comm/checkout/agreements.phtml');
        }
        return parent::_toHtml();
    }

    public function getMaxCommentSize()
    {
        if ($this->limitTextArea()) {
            return $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return '';
    }

    public function limitTextArea()
    {
        $result = false;
        if ($this->isCommentAllowed() &&
            $this->scopeConfig->isSetFlag('checkout/options/limit_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $value = $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (is_numeric($value)) {
                $result = true;
            }
        }
        return $result;
    }

    public function getAddressInstructions()
    {
        $session = $this->checkoutSession;
        /* @var $session Mage_Checkout_Model_Session */
        $addressId = $session->getQuote()->getShippingAddress()->getCustomerAddressId();
        $customerAddress = $this->customerAddressFactory->create()->load($addressId);
        return $customerAddress->getEccInstructions();
    }

    public function getRemainingCommentSize()
    {
        $max = $this->getMaxCommentSize();
        $current = $this->getAddressInstructions();
        return $max - strlen($current);
    }

    public function isCommentAllowed()
    {
        return $this->scopeConfig->getValue('checkout/options/show_comments', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}