<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Plugin;

/**
 * Description of ModifyBlockHtmlAfterPlugin
 *
 * @author ashwani.arya
 */
class ModifyBlockHtmlAfterPlugin {
    protected $commHelper;
    
    protected $customerSession;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
     
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->_urlBuilder = $urlBuilder;
    }
    
    
    /**
     * check for different blocks need to be disabled according to restriction
     *
     * @return array
     */
    public function afterToHtml(\Magento\Framework\View\Element\AbstractBlock $subject, $html) 
    {
       
        $block = $subject; //$observer->getEvent()->getBlock();
       
        $customerSession = $this->customerSession;
        /* @var $customerSession Magento\Customer\Model\Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $currentUrl = $this->_urlBuilder->getCurrentUrl();

        if ($block instanceof \Magento\Checkout\Block\Cart\Sidebar 
                || $block instanceof \Epicor\QuickOrderPad\Block\Cart 
                || $block instanceof \Epicor\Comm\Block\Cart\Quickadd 
                || $block instanceof \Epicor\Comm\Block\Cart\Product\Csvupload 
                || $block instanceof Mage_Checkout_Block_Cart_Minicart) {
            if (!$helper->canCustomerAccessUrl('checkout/cart') && !($block instanceof \Epicor\Comm\Block\Cart\Product\Csvupload && strpos($currentUrl, 'rfq') !== false)) {
               $html = '';
            }
        } else if ($block instanceof \Magento\Checkout\Block\Onepage\Link || $block instanceof Mage_Checkout_Block_Multishipping_Link) {
            /*if (!$helper->canCustomerAccessUrl('checkout/cart') || !$helper->canCustomerAccessUrl('checkout/')) {
                $html = '';
            } */
            if ($block instanceof Mage_Checkout_Block_Multishipping_Link && $helper->isFunctionalityDisabledForCustomer('multishipping')) {
               $html = '';
            } else if ($block instanceof Mage_Checkout_Block_Multishipping_Link && $helper->isMasquerading()) {
                $html = '';
            }
        } else if ($block instanceof \Magento\Wishlist\Block\Customer\Wishlist\Button) {
            if (strpos($block->getTemplate(), 'tocart') !== false) {
                if (!$helper->canCustomerAccessUrl('checkout/cart')) {
                   $html = '';
                }
            }
        } else if (($block instanceof \Magento\Catalog\Block\Product\Price || $block instanceof \Magento\Checkout\Block\Cart\Totals) && !($block instanceof \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option)) {
            if ($helper->isFunctionalityDisabledForCustomer('prices')) {
               $html = '';
            }
        }

        if ($block instanceof \Magento\Newsletter\Block\Subscribe) {
            if (!$helper->canCustomerAccessUrl('/newsletter/subscriber/new/')) {
               $html = '';
            }
        }

        if ($customer->isSupplier()) {
            if ($block instanceof \Magento\Newsletter\Block\Subscribe || $block instanceof \Epicor\Comm\Block\Page\SwitchBlock || $block instanceof \Magento\Directory\Block\Currency || $block->getNameInLayout() == 'footer_links' || $block->getNameInLayout() == 'footer_links2' || $block->getNameInLayout() == 'cms_footer_links') {
                $html = '';
            }
        }
        
        return $html;
    }
    
}
