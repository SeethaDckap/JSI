<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Customer\Render;

use Magento\Backend\Block\Context;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter as OptionsConverter;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * Checkbox constructor.
     * @param Context $context
     * @param OptionsConverter $converter
     * @param GroupCustomers $groupCustomers
     * @param array $data
     */
    public function __construct(
        Context $context,
        OptionsConverter $converter,
        GroupCustomers $groupCustomers,
        array $data = []
    ) {
        parent::__construct($context, $converter, $data);
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * Renders header of the column
     *
     * @return string
     */
    public function renderHeader()
    {
        $values = $this->_getValues();
        if ($this->getColumn()->getHeader()) {
            return parent::renderHeader();
        }

        if ($this->isDisabledSelectHeader()) {
            $this->getColumn()->setData('disabled', true);
        }

        $checked = '';
        $filter = $this->getColumn()->getFilter();
        if ($filter  && !empty($values)) {
            $checked = $filter->getValue() ? ' checked="checked"' : '';
        }

        $disabled = '';
        if ($this->getColumn()->getDisabled()) {
            $disabled = ' disabled="disabled"';
        }
        $html = '<th class="data-grid-th data-grid-actions-cell"><input type="checkbox" ';
        $html .= 'name="' . $this->getColumn()->getFieldName() . '" ';
        $html .= 'onclick="' . $this->getColumn()->getGrid()->getJsObjectName() . '.checkCheckboxes(this)" ';
        $html .= 'class="admin__control-checkbox"' . $checked . $disabled . ' ';
        $html .= 'title="' . __('Select All') . '"/><label></label></th>';
        return $html;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isDisabledSelectHeader()
    {
        $column = $this->getColumn();

        return !$this->groupCustomers->isEditableByCustomer('id', $column->getData('group_id'))
            && $column->getData('name') === 'selected_customers';
    }

    /**
     * Prepare data for renderer
     *
     * @return array
     */
    public function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}