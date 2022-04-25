<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Salesreps\Renderer;


/**
 * 
 * RFQ Sales rep editable text field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        $editable = false;

        if ($editable && $this->registry->registry('rfqs_editable')) {
            $html = '<input type="text" name="salesreps[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="salesreps_' . $index . '"/>';
        } else {
            $html = $value;
            $html .= '<input type="hidden" name="salesreps[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="salesreps_' . $index . '"/>';
        }

        return $html;
    }

}
