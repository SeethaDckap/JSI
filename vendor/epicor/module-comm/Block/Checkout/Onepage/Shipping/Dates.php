<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Onepage\Shipping;


class Dates extends \Magento\Checkout\Block\Onepage
{

    protected $_available_dates;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\Message\Request\DdaFactory
     */
    protected $commMessageRequestDdaFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\Message\Request\DdaFactory $commMessageRequestDdaFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commMessageRequestDdaFactory = $commMessageRequestDdaFactory;
    }
    protected function _construct()
    {
        $this->getCheckout()->setStepData('shipping_dates', array(
            'label' => __('Available Delivery Dates'),
            'is_show' => $this->isShow()
        ));
        parent::_construct();
    }

    public function isShow()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showAsList()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/showaslist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAvailableDates()
    {

        if (!isset($this->_available_dates)) {
            $dda = $this->commMessageRequestDdaFactory->create();
            /* @var $dda Epicor_Comm_Model_Message_Request_Dda */
            if ($dda->isActive()) {
                $dda->setQuote($this->getQuote());
                $dda->sendMessage();
            }
            $this->_available_dates = $dda->getDates();
        }
        return $this->_available_dates;
    }

    public function getDefaultAvailableDate()
    {
        $default_shipping_days = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $date = '1970-01-01'; //date('Y-m-d', strtotime('+'.$default_shipping_days.' days'));
        return $date;
    }

}
