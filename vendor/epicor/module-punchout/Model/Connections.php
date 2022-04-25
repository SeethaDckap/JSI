<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Punchout\Model;

use Magento\Framework\Model\AbstractModel;
use Epicor\Punchout\Api\Data\ConnectionsInterface;

/**
 * Connections Model.
 */
class Connections extends AbstractModel implements ConnectionsInterface
{


    /**
     *  Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Punchout\Model\ResourceModel\Connections');

    }//end _construct()


    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return parent::getData(self::NAME);

    }//end getName()


    /**
     * Get Is Active
     *
     * @return string|null
     */
    public function getIsActive()
    {
        return parent::getData(self::IS_ACTIVE);

    }//end getIsActive()


    /**
     * Get Created Date
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);

    }//end getCreatedAt()


    /**
     * Get Updated Date
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);

    }//end getUpdatedAt()


    /**
     * Set Name
     *
     * @param string $name Name.
     *
     * @return ConnectionsInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);

    }//end setName()


    /**
     * Set Is Active
     *
     * @param string $isActive Is active flag.
     *
     * @return ConnectionsInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);

    }//end setIsActive()


    /**
     * Set Created At
     *
     * @param string $createdAt Created at date.
     *
     * @return ConnectionsInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);

    }//end setCreatedAt()


    /**
     * Set Updated At
     *
     * @param string $updatedAt Updated at.
     *
     * @return ConnectionsInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);

    }//end setUpdatedAt()


    /**
     * Sets the 'scope_id' to website:storeviewid
     *
     * @return ConnectionsInterface
     */
    public function setScope()
    {
        return $this->setData('scope_id', $this->getWebsiteId().':'.$this->getStoreId());

    }//end setScope()


    /**
     * Get Connection Format
     *
     * @return string
     */
    public function getFormat()
    {
        return parent::getData(self::FORMAT);

    }//end getFormat()


    /**
     * Get Connection Identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return parent::getData(self::IDENTITY);

    }//end getIdentity()


    /**
     * Get Connection Default Shopper
     *
     * @return string
     */
    public function getDefaultShopper()
    {
        return parent::getData(self::DEFAULT_SHOPPER);

    }//end getDefaultShopper()


    /**
     * Get Website ID
     *
     * @return string
     */
    public function getWebsiteId()
    {
        return parent::getData(self::WEBSITE_ID);

    }//end getWebsiteId()


    /**
     * Get Store ID
     *
     * @return string
     */
    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);

    }//end getStoreId()


    /**
     * Set Name
     *
     * @param string $name Format type.
     *
     * @return ConnectionsInterface
     */
    public function setFormat($name)
    {
        return $this->setData(self::FORMAT, $name);

    }//end setFormat()


    /**
     * Set Name
     *
     * @param string $name Erp Account Code.
     *
     * @return ConnectionsInterface
     */
    public function setIdentity($name)
    {
        return $this->setData(self::IDENTITY, $name);

    }//end setIdentity()


    /**
     * Set Name
     *
     * @param integer $id Customer ID.
     *
     * @return ConnectionsInterface
     */
    public function setDefaultShopper($id)
    {
        return $this->setData(self::DEFAULT_SHOPPER, $id);

    }//end setDefaultShopper


    /**
     * Set Name
     *
     * @param integer $id Website ID.
     *
     * @return ConnectionsInterface
     */
    public function setWebsiteId($id)
    {
        return $this->setData(self::WEBSITE_ID, $id);

    }//end setWebsiteId()


    /**
     * Set Name
     *
     * @param integer $id Store ID.
     *
     * @return ConnectionsInterface
     */
    public function setStoreId($id)
    {
        return $this->setData(self::STORE_ID, $id);

    }//end setStoreId()


}//end class
