<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Quote\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

class Relation implements RelationInterface
{
    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object \Epicor\Customerconnect\Model\ArPayment\Quote\Address
         */
        if ($object->itemsCollectionWasSet()) {
            $object->getItemsCollection()->save();
        }
        if ($object->shippingRatesCollectionWasSet()) {
            $object->getShippingRatesCollection()->save();
        }
    }
}
