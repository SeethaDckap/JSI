<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\SalesRep\Api;

/**
 * Interface ShippingMethodManagementInterface
 * @api
 */
interface ErpShippingMethodManagementInterface
{
  
     /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address The estimate address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @deprecated
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address);

   /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param string $addressId The estimate address id
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddressId($cartId, $addressId);

    /**
     * Lists applicable shipping methods for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function getList($cartId);
  
}
