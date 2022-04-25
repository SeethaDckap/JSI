<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Order;

use Magento\Customer\Model\Context;

/**
 * Sales order view block
 */
class View extends \Magento\Sales\Block\Order\View
{
    /**
     * @var string
     */
    protected $_template = 'Epicor_Customerconnect::customerconnect/arpayments/order/view.phtml';
    
     /**
     * @return void
     */
    protected function _prepareLayout()
    {
        if ($this->getOrder()) {
            $this->pageConfig->getTitle()->set(__('AR PAYMENT REFERENCE # %1', $this->getOrder()->getRealOrderId()));
        }
    }
    
     /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/history');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return \Magento\Framework\Phrase
     */
    public function getBackTitle()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return __('Back to My Payments');
        }
        return __('View Another Payments');
    }
     
}
