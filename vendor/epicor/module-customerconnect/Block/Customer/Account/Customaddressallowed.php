<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account;


/**
 * Customer Address Allowed
 */
class Customaddressallowed extends \Epicor\Customerconnect\Block\Customer\Info
{

    /**
     * @var \Epicor\Comm\Model\Config\Source\Yesnonulloption
     */
    protected $yesnonullOption;


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Model\Config\Source\Yesnonulloption $yesnonulloption,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = [])
    {
        $this->yesnonullOption = $yesnonulloption;
        $this->customerSession = $customerSession;
        $this->commonHelper = $commonHelper;
        parent::__construct($context, $customerconnectHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/customaddressallowed.phtml');
    }

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getOption()
    {
        return $this->yesnonullOption;
    }
    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Epicor\Common\Helper\Data
     */
    public function getCommonHelper()
    {
        return $this->commonHelper;
    }
    //M1 > M2 Translation End
}
