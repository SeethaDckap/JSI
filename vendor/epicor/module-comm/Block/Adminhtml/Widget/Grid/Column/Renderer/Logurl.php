<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer;


/**
 * Log url renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Logurl extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
     * Render country grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());

        $html = '';

        if (!empty($data)) {
            if (strpos($data, 'comm/data') === false) {
                $html .= '<span style="display: block; white-space: nowrap; width: 200px; overflow-y: hidden;">' . $data . '</span>';
                $html .= '<a href="' . $data . '">Go to URL</a>';
            } else {
                $html .= 'System Url';
            }
        }

        return $html;
    }

}
