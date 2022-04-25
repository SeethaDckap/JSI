<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations;


/**
 * Locations Manufacturers renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Manufacturers extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $manufacturers = $row->getData($this->getColumn()->getIndex());
        $html = '';
        if (!empty($manufacturers)) {
            $manufacturers = unserialize($manufacturers);
            if (!empty($manufacturers)) {
                foreach ($manufacturers as $manufacturer) {
                    if ($manufacturer['primary'] == 'Y') {
                        $html .= '<strong>';
                    }

                    $html .= $manufacturer['name'];

                    if (!empty($manufacturer['product_code'])) {
                        $html .= ' | ' . __('SKU') . ': ' . $manufacturer['product_code'];
                    }

                    if ($manufacturer['primary'] == 'Y') {
                        $html .= '</strong>';
                    }

                    $html .= '<br />';
                }
            }
        }

        return $html;
    }

}
