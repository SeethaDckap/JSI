<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Epicor\Customerconnect\Model\ArPayment\Order\Address;

/**
 * Class Renderer used for formatting an order address
 * @api
 * @since 100.0.2
 */
class Renderer
{
    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Constructor
     *
     * @param AddressConfig $addressConfig
     * @param EventManager $eventManager
     */
    public function __construct(
        AddressConfig $addressConfig,
        EventManager $eventManager
    ) {
        $this->addressConfig = $addressConfig;
        $this->eventManager = $eventManager;
    }

    /**
     * Format address in a specific way
     *
     * @param Address $address
     * @param string $type
     * @return string|null
     */
    public function format($address, $type)
    {
        $this->addressConfig->setStore($address->getStoreId());
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        return $formatType->getRenderer()->renderArray($address->getData());
    }
}
