<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Attachments\Renderer;


/**
 * RFQ line attachments editable text field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $line = $this->registry->registry('current_rfq_row');
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        if ($this->registry->registry('rfqs_editable') || $this->registry->registry('rfqs_editable_partial')) {
            $html = '<input type="text" name="lineattachments[' . $key . '][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="line_attachments_' . $index . '"/>';
        } else {
            $html = $value;
        }

        return $html;
    }

}
