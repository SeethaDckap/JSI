<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Changes\Renderer;


/**
 * Expand column for changed orders list
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
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
        $html = '';

        $linesMsg = $row['lines'];
        $lines = false;
        if (isset($linesMsg['line'])) {
            $lines = $linesMsg['line'];
        }

        if (!empty($lines)) {
            $html = '<span class="plus-minus" id="changes-' . $row['id'] . '">+</span>';
        }

        return $html;
    }

}
