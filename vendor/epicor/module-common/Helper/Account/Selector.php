<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper\Account;


class Selector extends \Epicor\Common\Helper\Data
{

    public function getAccountTypes()
    {
        $types = $this->registry->registry('ecc_erp_account_types');

        if (!$types) {
            //M1 > M2 Translation Begin (Rule 4)
            //$typeNode = (array) Mage::getConfig()->getXpath('global/ecc_account_selector_types');
            //$types = array();

            //$typeData = array_pop($typeNode);

            $typeData = (array) $this->globalConfig->get('ecc_account_selector_types');
            //M1 > M2 Translation End

            foreach ($typeData as $type => $info) {
                $types[$type] = (array) $info;
            }

            $this->registry->register('ecc_erp_account_types', $types);
        }

        return $types;
    }

    public function getAccountTypesByPriority()
    {
        $types = $this->getAccountTypes();

        $sortedTypes = array();
        foreach ($types as $value => $type) {
            $type['value'] = $value;
            $sortedTypes[$type['priority']] = $type;
        }

        krsort($sortedTypes);

        return $sortedTypes;
    }

    public function getAccountTypeForCustomer($customer)
    {
        $accountType = '';
        $sortedTypes = $this->getAccountTypesByPriority();
        foreach ($sortedTypes as $type) {
            if (isset($type['field'])) {
                $accountId = $customer->getData($type['field']);
                if (!empty($accountId)) {
                    $accountType = $type['value'];
                    break;
                }
            } else if ($type['priority'] == 0) {
                $accountType = $type['value'];
            }
        }

        return $accountType;
    }

    public function getAccountTypeNames($ignore = null)
    {
        $acctTypes = $this->getAccountTypes();
        $acctype = array();
        foreach ($acctTypes as $key => $value) {
            $accType[$key] = $value['label'];
            if ($ignore == $key) {
                unset($accType[$key]);
            }
        }

        return $accType;
    }

}
