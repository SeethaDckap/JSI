<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Sales\Order\Shipment;


/**
 * Shipment_Track override
 * 
 * adds extra data to the getNumberDetail for use in the templates
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Track extends \Magento\Sales\Model\Order\Shipment\Track
{

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->shippingConfig = $shippingConfig;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $shipmentRepository,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Retrieve detail for shipment track
     *
     * @return string
     */
    public function getNumberDetail()
    {
        $carrierInstance = $this->shippingConfig->getCarrierInstance($this->getCarrierCode());
        if (!$carrierInstance) {
            $custom = $this->getData();
            $custom['number'] = $this->getTrackNumber();
            return $custom;
        } else {
            $carrierInstance->setStore($this->getStore());
        }

        if (!$trackingInfo = $carrierInstance->getTrackingInfo($this->getNumber())) {
            //M1 > M2 Translation Begin (Rule 55)
            //return __('No detail for number "%s"', $this->getNumber());
            return __('No detail for number "%1"', $this->getNumber());
            //M1 > M2 Translation End
        }

        return $trackingInfo;
    }

}
