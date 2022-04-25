<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Customer\Group;


/**
 * ERP Account hierarchy
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setParentId(int $value)
 * @method setChildId(int $value)
 * @method setType(string $value)
 * @method setCreatedAt(datetime $value)
 * @method setUpdatedAt(datetime $value)
 * 
 * @method int getParentId()
 * @method int getChildId()
 * @method string getType()
 * @method datetime getCreatedAt()
 * @method datetime getUpdatedAt()
 */
class Hierarchy extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'ecc_erp_account_hierarchy';
    protected $_eventObject = 'erp_customer_group_hierarchy';
    public static $linkTypes = array(
        'N' => 'National Account',
        'M' => 'Master Account',
        'B' => 'Bill To Account',
        'T' => 'Trading Account'
    );

    const LINK_TYPE_NATIONAL = 'N';
    const LINK_TYPE_MASTER = 'M';
    const LINK_TYPE_BILLTO = 'B';
    const LINK_TYPE_TRADING = 'T';

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy');
    }

    public function beforeSave()
    {
        //M1 > M2 Translation Begin (Rule 25)
        /*$this->setUpdatedAt(now());
        if ($this->isObjectNew()) {
            $this->setCreatedAt(now());
        }*/
        $this->setUpdatedAt(date('Y-m-d H:i:s'));
        if ($this->isObjectNew()) {
            $this->setCreatedAt(date('Y-m-d H:i:s'));
        }
        //M1 > M2 Translation End
        parent::beforeSave();
    }

}
