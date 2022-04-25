<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Returns\Details;


class Info extends \Epicor\Customerconnect\Block\Customer\Info
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
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        if ($return) {
            $helper = $this->customerconnectMessagingHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Messaging */

            $this->_infoData = array(
                //M1 > M2 Translation Begin (Rule 54)
                /*$this->__('Return Number :') => $return->getErpReturnsNumber() ?: $this->__('N/A'),
                $this->__('Customer Reference :') => $return->getCustomerReference() ?: $this->__('N/A'),
                //M1 > M2 Translation Begin (Rule 32)
                //$this->__('Created Date :') => $return->getRmaDate() ? $this->getHelper()->getLocalDate($return->getRmaDate(), \Epicor\Common\Helper\Data::DAY_FORMAT_MEDIUM, true) : $this->__('N/A'),
                $this->__('Created Date :') => $return->getRmaDate() ? $this->getHelper()->getLocalDate($return->getRmaDate(), \IntlDateFormatter::MEDIUM, true) : $this->__('N/A'),
                //M1 > M2 Translation End
                $this->__('Return Status :') => $return->getStatusDisplay(),
                $this->__('Customer Name :') => $return->getCustomerName(),
                $this->__('Credit Invoice Number :') => $return->getCreditInvoiceNumber() ?: $this->__('N/A'),
                $this->__('RMA Case Number :') => $return->getRmaCaseNumber() ?: $this->__('N/A'),*/
                __('Return Number')->render() => $return->getErpReturnsNumber() ?: __('N/A'),
                __('Customer Reference')->render() => $return->getCustomerReference() ?: __('N/A'),
                //M1 > M2 Translation Begin (Rule 32)
                //$this->__('Created Date :') => $return->getRmaDate() ? $this->getHelper()->getLocalDate($return->getRmaDate(), \Epicor\Common\Helper\Data::DAY_FORMAT_MEDIUM, true) : $this->__('N/A'),
                __('Created Date')->render() => $return->getRmaDate() ? $this->getHelper()->getLocalDate($return->getRmaDate(), \IntlDateFormatter::MEDIUM, true) : __('N/A'),
                //M1 > M2 Translation End
                __('Return Status')->render() => $return->getStatusDisplay(),
                __('Customer Name')->render() => $return->getCustomerName(),
                __('Credit Invoice Number')->render() => $return->getCreditInvoiceNumber() ?: __('N/A'),
                __('RMA Case Number')->render() => $return->getRmaCaseNumber() ?: __('N/A'),
                //M1 > M2 Translation End
            );

            if ($return->getErpSyncAction() != '') {
                //M1 > M2 Translation Begin (Rule 54)
                //$this->_infoData[$this->__('Erp Status :')] = $this->__('Awaiting Submission to ERP');
                $this->_infoData[__('Erp Status')->render()] = __('Awaiting Submission to ERP');
                //M1 > M2 Translation End
            }
        }
        $this->setTitle(__('Return Information'));
        $this->setColumnCount(2);
    }

}
