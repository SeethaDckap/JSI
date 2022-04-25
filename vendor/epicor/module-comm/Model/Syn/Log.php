<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Syn;


/**
 * Entity Register Model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method integer getEntityId()
 * @method string getMessage()
 * @method datetime getFromDate()
 * @method string getTypes()
 * @method string getBrands()
 * @method string getLanguages()
 * @method integer getCreatedById()
 * @method string getCreatedByName()
 * @method datetime getCreatedAt()
 * @method boolean getIsAuto()
 * 
 * @method setMessage(string $message)
 * @method setFromDate(datetime $date)
 * @method setTypes(string $types)
 * @method setBrands(string $brands)
 * @method setLanguages(string $languages)
 * @method setCreatedById(integer $id)
 * @method setCreatedByName(string $name)
 * @method setCreatedAt(datetime $date)
 * @method setIsAuto(boolean $isAuto)
 * 
 */
class Log extends \Epicor\Database\Model\Syn\Log
{

    protected $_eventPrefix = 'ecc_syn_log';
    protected $_eventObject = 'syn_log';
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    protected function _construct()
    {
        $this->_init('Epicor\Comm\Model\ResourceModel\Syn\Log');
    }

}
