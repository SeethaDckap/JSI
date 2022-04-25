<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\ResourceModel\Connections;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Menu resource model collection.
 */
class Collection extends AbstractCollection
{

    /**
     * Id field nam.
     *
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
        $this->_init('Epicor\Punchout\Model\Connections', 'Epicor\Punchout\Model\ResourceModel\Connections');

    }//end _construct()


}//end class
