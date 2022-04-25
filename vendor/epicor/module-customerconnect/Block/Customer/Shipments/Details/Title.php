<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Shipments\Details;


/**
 * Shipment Details page title
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends \Epicor\Customerconnect\Block\Customer\Title
{
    const FRONTEND_RESOURCE_REORDER = 'Epicor_Customerconnect::customerconnect_account_shipments_reorder';

    const FRONTEND_RESOURCE_RETURN = 'Epicor_Customerconnect::customerconnect_account_shipments_return';

    protected $_reorderType = 'Shipments';
    protected $_returnType = 'Shipment';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        parent::__construct(
            $context,
            $commonAccessHelper,
            $commReturnsHelper,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_setReorderUrl();
        $this->_setReturnUrl();
    }

    /**
     * Sets the Reorder link url
     */
    protected function _setReorderUrl()
    {
        $shipment = $this->registry->registry('customer_connect_shipments_details');
        if ($shipment) {
            $helper = $this->customerconnectHelper;

            $this->_reorderUrl = $helper->getShipmentReorderUrl($shipment, $this->_urlBuilder->getCurrentUrl());
        }
    }

    /**
     * Sets the Return link url
     */
    protected function _setReturnUrl()
    {
        $shipment = $this->registry->registry('customer_connect_shipments_details');
        if ($shipment) {
            $helper = $this->commReturnsHelper;

            $this->_returnUrl = $helper->getShipmentReturnUrl($shipment);
        }
    }

}
