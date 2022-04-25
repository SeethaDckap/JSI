<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;


class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if ($this->getColumn()->getLinks() == true) {
            $html = '';
            foreach ($actions as $action) {
                if (is_array($action)) {
                    if (!$this->matchesConditions($action, $row)) {
                        continue;
                    }
                    if (isset($action['conditions'])) {
                        unset($action['conditions']);
                    }
                    if ($html != '') {
                        $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                    }
                    $html .= $this->_toLinkHtml($action, $row);
                }
            }
            return $html;
        } else {
            return parent::render($row);
        }
    }

    /**
     * Checks for conditions if set
     * 
     * @param  array $action
     * @param  \Magento\Framework\DataObject $row
     * @return bool
     */
    private function matchesConditions($action, \Magento\Framework\DataObject $row)
    {
        if (!isset($action['conditions']) || !is_array($action['conditions'])) {
            return true;
        }

        foreach ($action['conditions'] as $field => $values) {
            $value = $row->getData($field);

            if (!is_array($values)) {
                $values = array($values);
            }

            if (in_array($value, $values) || (in_array('null', $values) && is_null($value)) || (in_array('empty', $values) && empty($value))) {
                continue;
            }

            return false;
        }


        return true;
    }

}
