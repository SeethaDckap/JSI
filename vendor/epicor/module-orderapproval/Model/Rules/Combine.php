<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Rules;

use Magento\Rule\Model\Condition\Context;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * Combine constructor.
     * @param GroupCustomers $groupCustomers
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        GroupCustomers $groupCustomers,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $orderApproval = array (
            0  => [
                'value' => 'Epicor\OrderApproval\Model\Rules\OrderValue|approval_limit',
                'label' => 'Total (Incl. Shipping and Tax)',
            ],
        );


        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                ['label' => __('Order Approval Rules'), 'value' => $orderApproval]
            ]
        );

        $additional = new \Magento\Framework\DataObject();
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }

    /**
     * @return Combine|\Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__new_child',
            'select',
            $this->getSelectProperties()
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Newchild::class)
        );
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSelectProperties()
    {
        $properties = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][new_child]',
            'values' => $this->getNewChildSelectOptions(),
            'value_name' => $this->getNewChildName(),
            'data-form-part' => $this->getFormName()
        ];

        if (!$this->groupCustomers->isEditableByCustomer('id')) {
            $properties['disabled'] = 'disabled';
        }

        return $properties;
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html =  'If <b>ALL</b> of these conditions are <b>True</b>:';
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }
}