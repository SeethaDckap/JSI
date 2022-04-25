<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model;

use Magento\Framework\Model\AbstractModel;
use Epicor\Lists\Api\Data\ImportInterface;

/**
 * Model Class for Lists
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 */
class Import extends AbstractModel implements ImportInterface
{
    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\Import');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getFileName()
    {
        return parent::getData(self::FILE_NAME);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return parent::getData(self::MESSAGES);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Group Id
     *
     * @param int $id
     *
     * @return \Epicor\Lists\Api\Data\ImportInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Name
     *
     * @param string $fileName
     *
     * @return \Epicor\Lists\Api\Data\ImportInterface
     */
    public function setFileName($fileName)
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }

    /**
     * Set Status
     *
     * @param string $status
     *
     * @return \Epicor\Lists\Api\Data\ImportInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set Message
     *
     * @param string $message
     *
     * @return \Epicor\Lists\Api\Data\ImportInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGES, $message);
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     *
     * @return \Epicor\Lists\Api\Data\ImportInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     *
     * @return \Epicor\Lists\Api\Data\ImportInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
