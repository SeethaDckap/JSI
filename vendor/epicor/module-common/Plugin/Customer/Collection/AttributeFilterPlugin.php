<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\Customer\Collection;


class AttributeFilterPlugin
{

    public function aroundAddAttributeToFilter(
        \Magento\Customer\Model\ResourceModel\Customer\Collection $subject,
        \Closure $proceed,
        $attribute,
        $condition = null,
        $joinType = 'inner')
    {
        if($subject->getFlag('is_supplier') || $subject->getFlag('is_salesrep')){
            return $proceed($attribute, $condition, $joinType);
        }
        $customerErpLinkTable = $subject->getTable('ecc_customer_erp_account');
        if($attribute == 'ecc_erpaccount_id'){
            if(!$subject->getFlag('erp_link_join')){
                if ($condition != null) {
                    $condition = 'erp.erp_account_id='.$condition;
                }
                $subject->joinTable(
                    ['erp' => $customerErpLinkTable],
                    'customer_id=entity_id',
                    ['erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code'],
                    $condition, 'inner'
                );
                $subject->setFlag('erp_link_join', 1);
            }else{
                $subject->getSelect()->where("erp.erp_account_id = ?",
                    $condition);
                $subject->setFlag('erp_link_join', 0);
            }
            return $subject;
        }else if($attribute == 'ecc_contact_code'){
            if(!$subject->getFlag('erp_link_join')){
                if ($condition != null) {
                    $condition = 'erp.contact_code=\''.$condition.'\'';
                }
                $subject->joinTable(
                    ['erp' => $customerErpLinkTable],
                    'customer_id=entity_id',
                    ['erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code'],
                    $condition, 'inner'
                );
                $subject->setFlag('erp_link_join', 1);
            }else{
                $subject->getSelect()->where("erp.contact_code = ?",
                    $condition);
                $subject->setFlag('erp_link_join', 0);
            }
            return $subject;
        }else{
            return $proceed($attribute, $condition, $joinType);
        }
    }
}
