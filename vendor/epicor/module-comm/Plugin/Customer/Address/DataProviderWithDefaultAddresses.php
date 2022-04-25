<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Customer\Address;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class DataProviderWithDefaultAddresses
{
    public function afterGetData(
        \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject,
        $result
    )
    {
        $collection = $subject->getCollection();
        $items = $collection->getItems();
        foreach ($items as $customer) {
            $erpAccounts = $customer->getErpAcctCounts();
            if (count($erpAccounts) > 1) {
                $result[$customer->getId()]['default_billing_address'] = false;
                $result[$customer->getId()]['default_shipping_address'] = false;
            }
        }
        return $result;
    }
}