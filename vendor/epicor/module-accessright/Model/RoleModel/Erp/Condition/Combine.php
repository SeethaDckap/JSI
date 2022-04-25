<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\RoleModel\Erp\Condition;

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
    protected $elementName = 'erp_rule';

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
        $this->_eventManager = $eventManager;
        parent::__construct($context, $data);
        $this->setType(\Epicor\AccessRight\Model\RoleModel\Erp\Condition\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $erpAttributes = array (
            0  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Erp\Condition\Erpaccount|allow_backorders',
                'label' => 'Allow Backorders',
            ],
            1  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Erp\Condition\Erpaccount|default_location_code',
                'label' => 'Default Location',
            ],
            2  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Erp\Condition\Erpaccount|is_branch_pickup_allowed',
                'label' => 'Branch Pickup Allowed',
            ],
            3  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Erp\Condition\Erpaccount|is_arpayments_allowed',
                'label' => 'AR Payment Allowed',
            ],
            4  => [
                'value' => 'Epicor\AccessRight\Model\RoleModel\Erp\Condition\Erpaccount|login_mode_type',
                'label' => 'Login Mode Type',
            ]
        );
        
        
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
//                [
//                    'value' => \Epicor\AccessRight\Model\RoleModel\Erp\Condition\Combine::class,
//                    'label' => __('Conditions Combination'),
//                ],
                ['label' => __('ERP Account Attributes'), 'value' => $erpAttributes]
            ]
        );
        
        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('erprule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }
        
        return $conditions;
    }
}