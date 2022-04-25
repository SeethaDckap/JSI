<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


class Number extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */
        $html = '';

        if (!$this->registry->registry('review_display')) {
            $html = '<input type="hidden" name="lines[' . $row->getUniqueId() . '][old_data]" value="' . base64_encode(serialize($row->getData())) . '" />';
            $html .= '<input type="hidden" name="lines[' . $row->getUniqueId() . '][source_data]" value="" />';
            $html .= '<input class="return_line_source_type" type="hidden" name="lines[' . $row->getUniqueId() . '][source_type]" value="' . $row->getSourceType() . '" />';
            $html .= '<input class="return_line_source_value" type="hidden" name="lines[' . $row->getUniqueId() . '][source_value]" value="' . $row->getData($row->getSourceType() . '_number') . '" />';
        }

        $lineCount = $this->registry->registry('line_count') ?: 1;

        if ($row->getToBeDeleted() == 'Y') {
            $html .= __('To Be Deleted');
        } else {
            $html .= '<span class="return_line_number">' . $lineCount . '</span>';

            $lineCount++;
            if ($this->registry->registry('line_count')) {
                $this->registry->unregister('line_count');
            }

            $this->registry->register('line_count', $lineCount);
        }
        return $html;
    }

}
