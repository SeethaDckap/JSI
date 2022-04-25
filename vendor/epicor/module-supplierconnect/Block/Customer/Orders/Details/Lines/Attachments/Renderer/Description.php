<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments\Renderer;


/**
 * Order line attachments editable text field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Description constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
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

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $line = $this->registry->registry('current_order_row');
        $key = 'existing';
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        if ($row->getWebFileId()) {
            $html = '<input type="text" name="lineattachments[' . $key . '][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="line_attachments_' . $index . '"/>';
        } else {
            $html = $value;
        }

        return $html;
    }

}
