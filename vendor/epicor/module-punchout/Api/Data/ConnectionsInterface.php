<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Api
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Api\Data;

/**
 * Punchout connections interface.
 * @api
 */
interface ConnectionsInterface
{

    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ENTITY_ID       = 'entity_id';
    const NAME            = 'connection_name';
    const FORMAT          = 'format';
    const IDENTITY        = 'identity';
    const DEFAULT_SHOPPER = 'default_shopper';
    const WEBSITE_ID      = 'website_id';
    const STORE_ID        = 'store_id';
    const IS_ACTIVE       = 'is_active';
    const CREATED_AT      = 'created_at';
    const UPDATED_AT      = 'updated_at';


    /**
     * Get Connection Id
     *
     * @return int|null
     */
    public function getEntityId();


    /**
     * Get Connection Name
     *
     * @return string
     */
    public function getName();


    /**
     * Get Connection Format
     *
     * @return string
     */
    public function getFormat();


    /**
     * Get Connection Identity
     *
     * @return string
     */
    public function getIdentity();


    /**
     * Get Connection Default Shopper
     *
     * @return string
     */
    public function getDefaultShopper();


    /**
     * Get Website ID
     *
     * @return string
     */
    public function getWebsiteId();


    /**
     * Get Store ID
     *
     * @return string
     */
    public function getStoreId();


    /**
     * Get Is Active
     *
     * @return string|null
     */
    public function getIsActive();


    /**
     * Get Created Date.
     *
     * @return string|null
     */
    public function getCreatedAt();


    /**
     * Get Updated Date.
     *
     * @return string|null
     */
    public function getUpdatedAt();


    /**
     * Set Name
     *
     * @param  string $name Name.
     *
     * @return ConnectionsInterface
     */
    public function setName($name);


    /**
     * Set Name
     *
     * @param  string $name Format type.
     *
     * @return ConnectionsInterface
     */
    public function setFormat($name);


    /**
     * Set Name
     *
     * @param  string $name Erp Account Code.
     *
     * @return ConnectionsInterface
     */
    public function setIdentity($name);


    /**
     * Set Name
     *
     * @param  integer $id Customer ID.
     *
     * @return ConnectionsInterface
     */
    public function setDefaultShopper($id);


    /**
     * Set Name
     *
     * @param  integer $id Website ID.
     *
     * @return ConnectionsInterface
     */
    public function setWebsiteId($id);


    /**
     * Set Name
     *
     * @param  integer $id Store ID.
     *
     * @return ConnectionsInterface
     */
    public function setStoreId($id);


    /**
     * Set Is Active
     *
     * @param string $isActive Is active flag.
     *
     * @return ConnectionsInterface
     */
    public function setIsActive($isActive);


    /**
     * Set Created At
     *
     * @param string $createdAt Created at date.
     *
     * @return ConnectionsInterface
     */
    public function setCreatedAt($createdAt);


    /**
     * Set Updated At
     *
     * @param string $updatedAt Updated at.
     *
     * @return ConnectionsInterface
     */
    public function setUpdatedAt($updatedAt);


}
