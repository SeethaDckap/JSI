<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


class Source extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->commReturnsHelper = $commReturnsHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $source = $row->getSourceType();

        switch ($source) {
            case 'order':
                if ($helper->getReturnUserType() != 'b2b') {
                    $sourceRef = $this->registry->registry('source_ref');
                    $order = !isset($sourceRef[$row->getOrderNumber()]) ? $helper->findLocalOrder($row->getOrderNumber()) : $sourceRef[$row->getOrderNumber()];
                    if ($order && !$order->getIsObjectNew()) {
                        $html = __('Order #') . $order->getIncrementId();
                    } else {
                        $html = __('Order #') . $row->getOrderNumber();
                    }
                    $sourceRef[$row->getOrderNumber()] = $order;
                    $this->registry->unregister('source_ref');
                    $sourceRef = $this->registry->register('source_ref', $sourceRef);
                } else {
                    $html = __('Order #') . $row->getOrderNumber();
                }
                break;
            case 'invoice':
                $html = __('Invoice #') . $row->getInvoiceNumber();
                break;
            case 'serial':
                $html = __('Serial #') . $row->getSerialNumber();
                break;
            case 'shipment':
                $html = __('Shipment #') . $row->getShipmentNumber();
                break;
            default:
                $html = __('SKU');
                break;
        }

        return '<span class="return_line_source_label">' . $html . '</span>';
    }

}
