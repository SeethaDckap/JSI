<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


/**
 * Return line row expand renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Expand extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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


    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<span class="plus-minus" id="return_line_attachments_' . $row->getUniqueId() . '">+</span>';
        return $html;
    }

}
