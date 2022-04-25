<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Addresses;

use Magento\Customer\Model\Context;

class Link extends \Magento\Framework\View\Element\Html\Link
{

    const FRONTEND_RESOURCE = 'Epicor_Checkout::checkout_choose_address';

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;
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
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_registration = $registration;
        $this->_accessauthorization = $context->getAccessAuthorization();
    }

    /**
     * @var array
     */
    public $changeAddressAllowed = true;

    protected function _toHtml()
    {
        /**
         * see Epicor\Lists\CustomerData\ChooseAddressLink::getSectionData()
         * calling from hole punching concept
         */
//        if (
//            $this->changeAddressAllowed &&
//            (!$this->_registration->isAllowed() || $this->httpContext->getValue(Context::CONTEXT_AUTH))
//            &&
//            $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE)
//        ) {
            return parent::_toHtml();
//        } else {
//            return '';
//        }
    }

}
