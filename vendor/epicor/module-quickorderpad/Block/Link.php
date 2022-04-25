<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Block;

class Link extends \Magento\Framework\View\Element\Html\Link
{


    const FRONTEND_RESOURCE = 'Epicor_Checkout::checkout_quick_order_pad';

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [])
    {
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct($context, $data);
    }

    /**
     * @var array
     */
    public $topLinkAllowed = true;

    protected function _toHtml()
    {
          // see data pool Epicor\QuickOrderPad\CustomerData\QuickOrderPadLink
//        if (
//            $this->topLinkAllowed &&
//            $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE)
//        ) {
            return parent::_toHtml();
//        } else {
//            return '';
//        }
    }

}
