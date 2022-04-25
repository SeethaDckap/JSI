<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote;

/**
 * Quotes collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\Customerconnect\Model\ArPayment\Quote::class, \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote::class);
    }
}
