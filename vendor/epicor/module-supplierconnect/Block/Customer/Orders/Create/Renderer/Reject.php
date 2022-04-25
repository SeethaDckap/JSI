<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Create\Renderer;


/**
 * Line comment display
 *
 * @author Pradeep.Kumar
 */
class Reject extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

        $disabled = (!$this->registry->registry('orders_editable')) ? 'disabled="disabled"' : '';

        $html = '<input type="checkbox" name="rejected[]" value="' . $row->getId() . '" id="po_reject_' . $row->getId() . '" class="po_reject" ' . $disabled . '/>';

        return $html;
    }

}
