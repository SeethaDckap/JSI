<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Cart;


/**
 * Lists customer Block
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Savecartaslist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $session,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->session = $session;
        $this->_accessauthorization = $context->getAccessAuthorization();
    }



    public function toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Checkout::checkout_checkout_cart_list'
        )) {
            return '';
        }
        return parent::toHtml();
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function cartHasItems()
    {
        return $this->session->getQuoteOnly()->getItemsCount()? true : false;
    }


}
