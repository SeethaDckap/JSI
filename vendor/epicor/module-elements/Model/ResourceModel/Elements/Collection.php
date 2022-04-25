<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model\ResourceModel\Elements;
/**
 * Elements 
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Epicor\Elements\Model\Elements',
            'Epicor\Elements\Model\ResourceModel\Elements'
        );
    }
}
