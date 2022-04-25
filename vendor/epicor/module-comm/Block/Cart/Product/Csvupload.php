<?php
/**
 * Copyright Â© 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Cart\Product;


/**
 * Product Csvupload block
 *
 * Adds products supplied in csv file to basket .
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Csvupload extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->cart = $cart;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Product Add to Basket by CSV'));
    }

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    public function getCart()
    {
        return $this->cart;
    }
    //M1 > M2 Translation End
    
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }    

}
