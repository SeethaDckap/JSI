<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Shipments\Details\Info;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Customerconnect\Block\Listing\Renderer\LinkorderFactory
     */
    protected $customerconnectListingRendererLinkorderFactory;

    /**
     * @var \Epicor\Common\Block\Renderer\CompositeFactory
     */
    protected $commonRendererCompositeFactory;

    /**
     * @var \Epicor\Customerconnect\Block\Listing\Renderer\LocationFactory
     */
    protected $customerconnectListingRendererLocationFactory;

    /**
     * @var \Epicor\Customerconnect\Block\Customer\Shipments\Details\Info\Renderer\TrackingnumberFactory
     */
    protected $customerconnectCustomerShipmentsDetailsInfoRendererTrackingnumberFactory;

    /**
     * @var \Epicor\Customerconnect\Block\Listing\Renderer\LinkinvoiceFactory
     */
    protected $customerconnectListingRendererLinkinvoiceFactory;

    /**
     * @var \Epicor\Customerconnect\Block\Listing\Renderer\InvoicestatusFactory
     */
    protected $customerconnectListingRendererInvoicestatusFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('customerconnect_shipping_info');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('cusd');
        $this->setIdColumn('order_number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $order = $this->registry->registry('customer_connect_shipments_details');
        if ($order) {
            $lines = ($order->getLines()) ? $order->getLines()->getasarrayLine() : array();

            if (!empty($lines)) {
                foreach ($lines as $line) {
                    $delivered = $line->getQuantity()->getDelivered();          // couldn't do direct as with quantity, so did it this way
                    $toFollow = $line->getQuantity()->getToFollow();
                    $line->setQuantity($line->getQuantity()->getOrdered());
                    $line->setDelivered($delivered);
                    $line->setToFollow($toFollow);
                }
            }

            $this->setCustomData((array)$lines);
        }
    }

    protected function _getColumns()
    {

        $columns = array(
            'order_number' => array(
                'header' => __('Order'),
                'align' => 'left',
                'index' => 'order_number',
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Linkorder',
                'type' => 'range',
            ),
            'quantity' => array(
                'header' => __('Order Qty'),
                'align' => 'left',
                'index' => 'quantity',
                'keys' => array(
                    'quantity',
                    'delivered',
                    'to_follow',
                ),
                'labels' => array(
                    'quantity' => 'Quantity',
                    'delivered' => 'Delivered',
                    'to_follow' => 'To Follow',
                ),
                'join' => '<br />',
                'renderer' => 'Epicor\Common\Block\Renderer\Composite',
                'type' => 'text'
            ),
            'delivered' => array(
                'header' => __('Delivered'),
                'align' => 'left',
                'index' => 'delivered',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
                'type' => 'text'
            ),
            'to_follow' => array(
                'header' => __('To Follow'),
                'align' => 'left',
                'index' => 'to_follow',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
                'type' => 'text'
            ),
            'location' => array(
                'header' => __('Location'),
                'align' => 'left',
                'index' => 'location_code',
                'type' => 'text',
                'filter' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Location',
            ),
            'unit_of_measure_description' => array(
                'header' => __('UOM'),
                'align' => 'left',
                'index' => 'unit_of_measure_description',
                'type' => 'text'
            ),
            'product_code' => array(
                'header' => __('Part Number'),
                'align' => 'left',
                'index' => 'product_code',
                'type' => 'text'
            ),
            'description' => array(
                'header' => __('Description'),
                'align' => 'left',
                'index' => 'description',
                'type' => 'text'
            ),
            'tracking_number' => array(
                'header' => __('Tracking Number'),
                'align' => 'left',
                'column_css_class' => 'tracking_number',
                'index' => 'tracking_number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Shipments\Details\Info\Renderer\Trackingnumber',
                'type' => 'range'
            ),
            'invoice_number' => array(
                'header' => __('Invoice Number'),
                'align' => 'left',
                'index' => 'invoice_number',
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Linkinvoice',
                'type' => 'range'
            ),
            'invoice_status' => array(
                'header' => __('Invoice Status'),
                'align' => 'left',
                'index' => 'invoice_status',
                'type' => 'text',
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Invoicestatus'
            ),
        );

        $locHelper = $this->commLocationsHelper;
        $showLoc = ($locHelper->isLocationsEnabled()) ? $locHelper->showIn('cc_shipments') : false;

        if (!$showLoc) {
            unset($columns['location']);
        }

        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
