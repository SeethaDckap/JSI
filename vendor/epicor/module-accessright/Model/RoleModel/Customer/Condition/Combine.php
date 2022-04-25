<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\RoleModel\Customer\Condition;

/**
 * @api
 * @since 100.0.2
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{

    /**
     * Base name for hidden elements.
     *
     * @var string
     */
     protected $elementName = 'customer_rule';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
     protected $_eventManager;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->setType(\Epicor\AccessRight\Model\RoleModel\Customer\Condition\Combine::class);
        $this->_eventManager = $eventManager;
        parent::__construct($context, $data);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $customerAttributes = array (

            0  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Customer\Condition\CustomerAccount|ecc_master_shopper',
                'label' => 'Master Shopper',
            ],
            1  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Customer\Condition\CustomerAccount|ecc_hide_price',
                'label' => 'Hide Price',
            ],
            2  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Customer\Condition\CustomerAccount|ecc_function',
                'label' => 'Function',
            ],
            3  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Customer\Condition\CustomerAccount|ecc_is_branch_pickup_allowed',
                'label' => 'Branch Pickup Allowed',
            ],
            4  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Customer\Condition\CustomerAccount|ecc_login_mode_type',
                'label' => 'Login Mode Type',
            ]
        );
        
        
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
//                [
//                    'value' => \Epicor\AccessRight\Model\RoleModel\Customer\Condition\Combine::class,
//                    'label' => __('Conditions Combination'),
//                ],
                ['label' => __('Customer Attributes'), 'value' => $customerAttributes]
            ]
        );
        
        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('customerrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}