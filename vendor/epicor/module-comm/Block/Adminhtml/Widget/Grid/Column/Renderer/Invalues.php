<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Country column renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Invalues extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render country grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $values = $this->getColumn()->getValues();
        $options = array_values($this->getColumn()->getOptions());

        if (!is_array($values)) {
            $values = array($values);
        }

        if (!is_array($options)) {
            $options = array('', $options);
        } elseif (count($options) == 0) {
            $options = array('', '');
        } elseif (count($options) == 1) {
            $options = array('', $options[0]);
        }

        $option = in_array($value, $values) ? 1 : 0;
        return $options[$option];
    }

}
