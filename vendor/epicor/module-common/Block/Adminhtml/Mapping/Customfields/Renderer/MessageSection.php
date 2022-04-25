<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Customfields\Renderer;


/**
 * Customer Account Type Grid Renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class MessageSection extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $globalConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        array $data = []
    ) {
        $this->globalConfig = $globalConfig;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     * 
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $row->getMessageSection();
        if($status =="grid_config") {
            $statusCode = "Grid Config";
        } elseif ($status =="address_section") {
            $statusCode = "Address Section";
        } elseif ($status =="information_section") {
            $statusCode = "Information Section";
        } elseif ($status =="original_grid_setup") {
            $statusCode = "Original Grid Setup";
        } elseif ($status =="replacement_grid_setup") {
            $statusCode = "Replacements Grid Setup";
        } elseif ($status =="new_po_set_grid") {
            $statusCode = "New PO Grid Setup";
        }
        return $statusCode;
    }

}
