<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Cart;

/**
 * Cart sidebar block
 *
 * @api
 * @since 100.0.2
 */
class Sidebar extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Checkout\Block\Cart\Sidebar
     */
    protected $_cartSidebar;
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Block\Cart\Sidebar $sidebar,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_cartSidebar = $sidebar;
        $this->commHelper = $commHelper;
    }

    /**
     * Get serialized config
     *
     * @return string
     * @since 100.2.0
     */
    public function getSerializedConfig()
    {
        $result='{}';
        if(!$this->commHelper->canCustomerAccessUrl('checkout/cart')){
         //   echo $this->_cartSidebar->toHtml(); exit;
            $result=$this->_cartSidebar->getSerializedConfig();

        }
        return $result;
    }
}
