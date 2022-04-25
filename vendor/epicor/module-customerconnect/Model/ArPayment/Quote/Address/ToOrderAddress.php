<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\Quote\Address;

use Magento\Framework\DataObject\Copy;
use Epicor\Customerconnect\Model\ArPayment\Quote\Address;
use Epicor\Customerconnect\Model\ArPayment\Order\AddressRepository as OrderAddressRepository;
use Epicor\Customerconnect\Api\Data\OrderAddressInterface;

/**
 * Class ToOrderAddress
 */
class ToOrderAddress
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderAddressRepository
     */
    protected $orderAddressRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderAddressRepository $orderAddressRepository
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderAddressRepository $orderAddressRepository,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderAddressRepository = $orderAddressRepository;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderAddressInterface
     */
    public function convert(Address $object, $data = [])
    {
        $orderAddress = $this->orderAddressRepository->create();

        $orderAddressData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_order_address',
            $object
        );
        $this->dataObjectHelper->populateWithArray(
            $orderAddress,
            array_merge($orderAddressData, $data),
            \Epicor\Customerconnect\Api\Data\OrderAddressInterface::class
        );
        return $orderAddress;
    }
}
