<?php

namespace Cloras\Mageorders\Model;

use Cloras\Mageorders\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'cls_orders_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'cls_orders_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'cls_orders_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Cloras\Mageorders\Model\ResourceModel\Grid');
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($Id)
    {
        return $this->setData(self::ID, $Id);
    }

    /**
     * Get OrderId.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::OrderId);
    }

    /**
     * Set OrderId.
     */
    public function setOrderId($OrderId)
    {
        return $this->setData(self::OrderId, $OrderId);
    }

    /**
     * Get getCustomerId.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CustomerId);
    }

    /**
     * Set CustomerId.
     */
    public function setCustomerId($CustomerId)
    {
        return $this->setData(self::CustomerId, $CustomerId);
    }

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::Status);
    }

    /**
     * Set Status.
     */
    public function setStatus($Status)
    {
        return $this->setData(self::Status, $Status);
    }

    /**
     * Get State.
     *
     * @return varchar
     */
    public function getState()
    {
        return $this->getData(self::State);
    }

    /**
     * Set State.
     */
    public function setState($State)
    {
        return $this->setData(self::State, $State);
    }

    /**
     * Get UpdatedAt.
     *
     * @return varchar
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UpdatedAt);
    }

    /**
     * Set UpdatedAt.
     */
    public function setUpdatedAt($UpdatedAt)
    {
        return $this->setData(self::UpdatedAt, $UpdatedAt);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CreatedAt);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($CreatedAt)
    {
        return $this->setData(self::CreatedAt, $CreatedAt);
    }
}
