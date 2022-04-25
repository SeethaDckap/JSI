<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Shipments\Details;


class Date extends \Epicor\Customerconnect\Block\Customer\Info
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_shipments_details';

    // extended block_customer_info to get the helper


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    // extended block_customer_info to get the helper
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $data = $this->registry->registry('customer_connect_shipments_details');
        if ($data) {
            preg_match('/T00:00:00/', $data->getShipmentDate(), $inValidTime);
            $allowTime = $inValidTime ? false : true;

            $this->_infoData = array(
                //M1 > M2 Translation Begin (Rule 32)
                //$this->__('Shipment Date :') => $data->getShipmentDate() ? $this->getHelper()->getLocalDate($data->getShipmentDate(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, $allowTime) : $this->__('N/A'),
                __('Shipment Date')->render() => $data->getShipmentDate() ? $this->getHelper()->getLocalDate($data->getShipmentDate(), \IntlDateFormatter::MEDIUM, $allowTime) : __('N/A'),
                //M1 > M2 Translation End
                __('Ship Via')->render() => $data->getDeliveryMethod(),
            );
        }
        $this->setTitle(__('Packing Slip Information'));
        $this->setColumnCount(2);
    }

}
