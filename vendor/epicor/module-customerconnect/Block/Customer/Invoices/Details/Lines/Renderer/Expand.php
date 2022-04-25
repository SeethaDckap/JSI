<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Shipping
 *
 * @author Paul.Ketelle
 */
class Expand extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_invoices_misc';
    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $html = '';
        $expand = '+';

        $shipmentMsg = $row->getShipments();
        $shipments = false;

        $showMiscCharges = $this->canShowMisc();
        $defaultMiscView = $this->customerconnectHelper->checkCusMiscView();
        $miscellaneousCharges = $row->getMiscellaneousCharges() ? $row->getMiscellaneousCharges()->getasarrayMiscellaneousLine() : array();
        if (!empty($shipmentMsg)) {
            $shipments = $shipmentMsg->getShipment();
        }

        if($defaultMiscView){
            $expand = '-';
        }

        $row->setUniqueId(uniqid());

        if ($shipments || (!empty($miscellaneousCharges) && $showMiscCharges && $row->getMiscellaneousChargesTotal())) {
            $html = '<span class="plus-minus" id="misc-' . $row->getUniqueId() . '">'.$expand.'</span>';
        }
        return $html;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ);
        return $showMiscCharges && $isMiscAllowed;
    }

}
