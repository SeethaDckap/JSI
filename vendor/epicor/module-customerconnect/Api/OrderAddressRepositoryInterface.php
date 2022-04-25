<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Api;

/**
 * Order address repository interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 100.0.2
 */
interface OrderAddressRepositoryInterface
{

    /**
     * Loads a specified order address.
     *
     * @param int $id The order address ID.
     * @return \Epicor\Customerconnect\Api\Data\OrderAddressInterface Order address interface.
     */
    public function get($id);

    /**
     * Deletes a specified order address.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity The order address.
     * @return bool
     */
    public function delete(\Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity);

    /**
     * Performs persist operations for a specified order address.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity The order address.
     * @return \Epicor\Customerconnect\Api\Data\OrderAddressInterface Order address interface.
     */
    public function save(\Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity);
}
