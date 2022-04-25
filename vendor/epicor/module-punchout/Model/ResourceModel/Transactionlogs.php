<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Transaction Logs resource model.
 */
class Transactionlogs extends AbstractDb
{

    const TABLE_NAME = 'ecc_punchout_log';

    /**
     * Table initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');

    }//end _construct()


}
