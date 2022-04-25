<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Header;

class Togglelink extends \Magento\Framework\View\Element\Html\Link {

    /**
     * @var array
     */
    public $topLinkAllowed = true;

    /**
     * @var array
     */
    public $dealerHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Togglelink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Epicor\Dealerconnect\Helper\Data $dealerHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        /**
         * see Epicor\Dealerconnect\CustomerData\DelearShopperLink for FPC
         * checking condition with hole punch
         */

        return parent::_toHtml();
    }

    /**
     * @return mixed
     */
    public function getCurrentMode()
    {
        return $this->customerSession->getDealerCurrentMode();
    }

    /**
     * @param string $type
     * @return string
     */
    public function getImagePath($type = "dealer")
    {
        if ($type == "dealer") {
            return $this->getViewFileUrl("Epicor_Dealerconnect::epicor/dealerconnect/images/small-icon-customer.png");
        } elseif ($type == "shopper") {
            return $this->getViewFileUrl("Epicor_Dealerconnect::epicor/dealerconnect/images/small-icon-dealer.png");
        }
        return "";
    }

}
