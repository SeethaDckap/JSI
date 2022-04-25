<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\ResourceModel\Transactionlogs;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Menu resource model collection.
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Epicor\Punchout\Model\Transactionlogs', 'Epicor\Punchout\Model\ResourceModel\Transactionlogs');

    }//end _construct()


    /**
     * Joining the Conenction table.
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['connections' => $this->getTable('ecc_punchout_connections')],
            'main_table.connection_id = connections.entity_id',
            'connection_name'
        );

        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('connection_name', 'connection_name');
    }
}//end class
