<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\BranchPickup\Block;

use Magento\Customer\Model\Context;

class Link extends \Magento\Framework\View\Element\Html\Link
{

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $_registration;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchpickupHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Registration $registration,
        \Epicor\BranchPickup\Helper\Data $branchpickupHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_registration = $registration;
        $this->branchpickupHelper = $branchpickupHelper;
    }

    protected function _toHtml()
    {
//        if (!$this->_registration->isAllowed() || $this->httpContext->getValue(Context::CONTEXT_AUTH)) {
//            if ($this->branchpickupHelper->isBranchPickupAvailable()) {

                return parent::_toHtml();
//            } else {
//                return '';
//            }
//        } else {
//            if ($this->branchpickupHelper->isBranchPickupAvailable()) {
//                return parent::_toHtml();
//            }
//        }
    }
}
