<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\parts\Listing\Renderer;

/**
 * RFQ line attachments column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Uom extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Epicor\Lists\Helper\Messaging
     */
    protected $listsMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Epicor\Lists\Helper\Messaging $listsMessagingHelper, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->listsMessagingHelper = $listsMessagingHelper;
        $this->registry = $registry;
        parent::__construct(
                $context, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {
        $helper = $this->listsMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $html = '';

        $colspan = 7;
        $productCode = $row->getProductCode();
        $uoms = $row->getUnitOfMeasures()->getasarrayUnitOfMeasure();
        $uomArray = array();
        if (!empty($uoms)) {                      // sort uoms by code
            foreach ($uoms as $uom) {
                $uomArray[$uom->getUnitOfMeasureCode()] = $uom;
            }
            ksort($uomArray);
        }
        $id = 'parts_row_uom_' . $productCode;
        $html = '<span id="part_uom_col_' . $productCode . '">+</td>'
                . '</tr>'
                . '<td colspan="' . $colspan . '" id="' . $id . '" style="display: none;">';

        if ($this->registry->registry('contracts_parts_row')) {
            $this->registry->unregister('contracts_parts_row');
        }

        $this->registry->register('contracts_parts_row', $uomArray);

//        $block = $this->getLayout()->createBlock('\Epicor\Lists\Block\Customer\Account\Contracts\Parts\Uom');
//        /* @var $block Epicor_Lists_Block_Customer_Account_Contracts_Parts_Uom */
//
//        $html .= $block->toHtml();

        return $html;
    }

}
