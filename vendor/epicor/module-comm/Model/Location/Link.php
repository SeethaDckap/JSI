<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Location;


/**
 * Location link model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setEntityId(integer $value)
 * @method setEntityType(string $value)
 * @method setLocationCode(string $value)
 * @method setCreatedAt(datetime $value)
 * @method setUpdatedAt(datetime $value)
 * @method setLinkType(string $value)
 * 
 * @method integer getEntityId()
 * @method string getEntityType()
 * @method string getLocationCode()
 * @method datetime getCreatedAt()
 * @method datetime getUpdatedAt()
 * @method string getLinkType()
 */
class Link extends \Epicor\Common\Model\AbstractModel
{

    const ENTITY_TYPE_STORE = 'store';
    const ENTITY_TYPE_ERPACCOUNT = 'erpaccount';
    const ENTITY_TYPE_CUSTOMER = 'customer';
    const ENTITY_TYPE_ERPADDRESS = 'erpaddress';
    const LINK_TYPE_INCLUDE = 'I';
    const LINK_TYPE_EXCLUDE = 'E';

    public static function getEntityTypes()
    {
        return array(
            self::ENTITY_TYPE_STORE,
            self::ENTITY_TYPE_ERPACCOUNT,
            self::ENTITY_TYPE_CUSTOMER,
            self::ENTITY_TYPE_ERPADDRESS
        );
    }

    protected $_eventPrefix = 'ecc_location_link';
    protected $_eventObject = 'location_link';

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Link');
    }

}
