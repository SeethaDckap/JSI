<?php

namespace Cloras\Mageorders\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const ORDER_ID = 'order_id';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';
    const STATE = 'state';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

   /**
    * Get Id.
    *
    * @return int
    */
    public function getId();

   /**
    * Set Id.
    */
    public function setId($Id);

   /**
    * Get ORDER ID.
    *
    * @return int
    */
    public function getOrderId();

   /**
    * Set ORDER ID.
    */
    public function setOrderId($OrderId);

   /**
    * Get CUSTOMER ID.
    *
    * @return int
    */
    public function getCustomerId();

   /**
    * Set CUSTOMER ID.
    */
    public function setCustomerId($CustomerId);

   /**
    * Get STATUS.
    *
    * @return varchar
    */
    public function getStatus();

   /**
    * Set STATUS.
    */
    public function setStatus($Status);

   /**
    * Get STATE.
    *
    * @return varchar
    */
    public function getState();

   /**
    * Set STATE.
    */
    public function setState($State);

   /**
    * Get UPDATED AT.
    *
    * @return varchar
    */
    public function getUpdatedAt();

   /**
    * Set UPDATED AT.
    */
    public function setUpdatedAt($UpdatedAt);

   /**
    * Get Created At.
    *
    * @return varchar
    */
    public function getCreatedAt();

   /**
    * Set CreatedAt.
    */
    public function setCreatedAt($CreatedAt);
}
