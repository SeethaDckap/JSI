<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ line is kit column renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Iskit extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
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
        $html = '<span class="is_kit_display">' . $value . '</span>';
        $html .= '<input class="lines_is_kit" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][is_kit]" value="' . $value . '" /> ';

        return $html;
    }

}
