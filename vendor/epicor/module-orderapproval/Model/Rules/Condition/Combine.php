<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Model\Rules\Condition;

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
    protected $elementName = 'group';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Combine constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context     $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($context, $data);
        $this->setType(\Epicor\OrderApproval\Model\Rules\Condition\Combine::class);
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
                'value' => 'Epicor\OrderApproval\Model\Rules\Condition|total',
                'label' => 'Total (Incl. Shipping and Tax)',
            ]
        );
        
        
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
//                [
//                    'value' => \Epicor\OrderApproval\Model\Rules\Condition\Combine::class,
//                    'label' => __('Conditions Combination'),
//                ],
                ['label' => __('Order Approval Rules'), 'value' => $erpAttributes]
            ]
        );
        
        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('rules_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }
        
        return $conditions;
    }

    /**
     * Load aggregation options
     *
     * @return $this
     */
    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption(['all' => __('ALL')]);
        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([1 => __('TRUE')]);
        return $this;
    }
}