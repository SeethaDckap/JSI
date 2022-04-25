<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Returns\Details;


class Statusinfo extends \Epicor\Customerconnect\Block\Customer\Info
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('customerconnect/customer/account/returns/status.phtml');

        $this->setTitle(__('Status Information :'));
        $this->setColumnCount(1);
    }

    public function getReturnMapping()
    {
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $helper = $this->customerconnectMessagingHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Messaging */

        return $helper->getRmaStatusMapping($return->getReturnsStatus());
    }

}
