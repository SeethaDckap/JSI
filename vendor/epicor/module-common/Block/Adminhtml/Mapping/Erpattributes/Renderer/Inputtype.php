<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Renderer;


/**
 * Amend input types for epicor_comm/erp_mapping_attributes 
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Inputtype extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
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
        $options = $this->commHelper->_getEccattributeTypes();
        $x = $row->getInputType();
        return $options[$row->getInputType()];
    }

}
