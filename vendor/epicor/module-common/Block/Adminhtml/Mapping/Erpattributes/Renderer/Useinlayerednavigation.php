<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Renderer;


/**
 * display actual labels for use in layered navigation column in epicor_comm/erp_mapping_attributes
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Useinlayerednavigation extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
     * Render column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $options = array(
            array('value' => '0', 'label' => __('No')),
            array('value' => '1', 'label' => __('Filterable (with results)')),
            array('value' => '2', 'label' => __('Filterable (no results)')),
        );
        return $options[$row->getData($this->getColumn()->getIndex())]['label'];
    }

}
