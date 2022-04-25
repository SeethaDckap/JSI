<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Api;

/**
 * Order payment repository interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 100.0.2
 */
interface OrderPaymentRepositoryInterface
{
 
    /**
     * Loads a specified order payment.
     *
     * @param int $id The order payment ID.
     * @return \Epicor\Customerconnect\Api\Data\OrderPaymentInterface Order payment interface.
     */
    public function get($id);

    /**
     * Deletes a specified order payment.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return bool
     */
    public function delete(\Epicor\Customerconnect\Api\Data\OrderPaymentInterface $entity);

    /**
     * Performs persist operations for a specified order payment.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return \Epicor\Customerconnect\Api\Data\OrderPaymentInterface Order payment interface.
     */
    public function save(\Epicor\Customerconnect\Api\Data\OrderPaymentInterface $entity);

    /**
     * Creates new Order Payment instance.
     *
     * @return \Epicor\Customerconnect\Api\Data\OrderPaymentInterface Transaction interface.
     */
    public function create();
}
