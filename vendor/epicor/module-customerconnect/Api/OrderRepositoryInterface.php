<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Api;

/**
 * Order repository interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 100.0.2
 */
interface OrderRepositoryInterface
{
 
    /**
     * Loads a specified order.
     *
     * @param int $id The order ID.
     * @return \Epicor\Customerconnect\Api\Data\OrderInterface Order interface.
     */
    public function get($id);

    /**
     * Deletes a specified order.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderInterface $entity The order ID.
     * @return bool
     */
    public function delete(\Epicor\Customerconnect\Api\Data\OrderInterface $entity);

    /**
     * Performs persist operations for a specified order.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderInterface $entity The order ID.
     * @return \Epicor\Customerconnect\Api\Data\OrderInterface Order interface.
     */
    public function save(\Epicor\Customerconnect\Api\Data\OrderInterface $entity);
}
