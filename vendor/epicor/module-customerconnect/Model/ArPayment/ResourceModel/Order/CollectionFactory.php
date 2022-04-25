<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order;

/**
 * Class CollectionFactory
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     */
    public function create($customerId = null)
    {
        /** @var \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Collection $collection */
        $collection = $this->objectManager->create($this->instanceName);

        if ($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        }

        return $collection;
    }
}
