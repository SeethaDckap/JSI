<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model;

use Magento\Framework\Model\AbstractModel;

class ManageDashboard extends AbstractModel
{

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Epicor\Supplierconnect\Model\ResourceModel\ManageDashboard');
    }

}
