<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Changes\Renderer;


/**
 * Lines list display (expanded by expand column)
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Lines extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;

        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->supplierconnectHelper;
        /* @var $helper Epicor_Supplierconnect_Helper_Data */

        $html = '';
        $lines = isset($row['lines']) ? $row['lines'] : array();
        $disabled = (!$this->registry->registry('orders_editable')) ? 'disabled="disabled"' : '';

        if (count($lines) > 0) {
            $html = '</td></tr><tr id="row-changes-' . $row->getId() . '" style="display: none;">
            <td colspan="9" class="lines-row">
            <table class="expand-table">
                <thead>
                    <tr class="headings">
                        <th>' . __('Confirm') . '</th>
                        <th>' . __('Reject') . '</th>
                        <th>' . __('PO Line') . '</th>
                        <th>' . __('Release Number') . '</th>
                        <th>' . __('Part Number') . '</th>
                        <th>' . __('Supplier Part number') . '</th>
                        <th>' . __('Order Qty') . '</th>
                        <th>' . __('Orig Release Qty') . '</th>
                        <th>' . __('New Release Qty') . '</th>
                        <th>' . __('Orig Due Date') . '</th>
                        <th>' . __('New Due Date') . '</th>
                    </tr>
                </thead>
                <tbody>
            ';
            //echo '<pre>'; print_r($lines); exit;
            if(isset($lines['line'][0])){
                $lines=$lines['line'];
            }
            foreach ($lines as $line) {
                $origDueDate = isset($line['origValues']['dueDate']) ?
                    $line['origValues']['dueDate'] : [];
                $origDueDate = !empty($origDueDate) ?
                    $helper->getLocalDate(
                        $origDueDate, \IntlDateFormatter::MEDIUM
                    ) : __('N/A');

                $newDueDate = isset($line['newValues']['dueDate']) ?
                    $line['newValues']['dueDate'] : [];

                $newDueDate = !empty($newDueDate) ?
                    $helper->getLocalDate(
                        $newDueDate, \IntlDateFormatter::MEDIUM
                    ) : __('N/A');
                $emptyvalue = '';
                $purchaseOrderLineNumber = isset($line['purchaseOrderLineNumber']) &&
                !empty($line['purchaseOrderLineNumber']) ?
                    $line['purchaseOrderLineNumber'] : $emptyvalue;
                $releaseNumber = isset($line['releaseNumber']) && !empty($line['releaseNumber']) ?
                    $line['releaseNumber'] : $emptyvalue;
                $productCode = isset($line['productCode']) && !empty($line['productCode']) ?
                    $line['productCode'] : $emptyvalue;
                $supplierProductCode = isset($line['supplierProductCode']) && !empty($line['supplierProductCode']) ?
                    $line['supplierProductCode'] : $emptyvalue;
                $quantity = isset($line['quantity']) && !empty($line['quantity']) ?
                    $line['quantity'] : $emptyvalue;
                $origValues = isset($line['origValues']['releaseQuantity']) &&
                !empty($line['origValues']['releaseQuantity']) ?
                    $line['origValues']['releaseQuantity'] : $emptyvalue;
                $newValues = isset($line['newValues']['releaseQuantity']) &&
                !empty($line['newValues']['releaseQuantity']) ?
                    $line['newValues']['releaseQuantity'] : $emptyvalue;

                $html .= '
                  <tr>
        <td><input type="checkbox" 
        name="actions[' . $row['id'] . '][' . $line['purchaseOrderLineNumber'] . '][' . $line['releaseNumber'] . ']" 
            value="C" 
            id="po_confirm_' . $row['id'] . '_' . $line['purchaseOrderLineNumber'] . '_' . $line['releaseNumber'] . '" 
                    class="po_confirm" ' . $disabled . '/></td>
                    
        <td><input type="checkbox" 
        name="actions[' . $row['id'] . '][' . $line['purchaseOrderLineNumber'] . '][' . $line['releaseNumber'] . ']" 
            value="R" 
            id="po_reject_' . $row['id'] . '_' . $line['purchaseOrderLineNumber'] . '_' . $line['releaseNumber'] . '" 
                    class="po_reject" ' . $disabled . '/></td>                    
                    <td>' . $purchaseOrderLineNumber . '</td>
                    <td>' . $releaseNumber . '</td>
                    <td>' . $productCode . '</td>
                    <td>' . $supplierProductCode . '</td>
                    <td>' . $quantity . '</td>
                    <td>' . $origValues . '</td>
                    <td>' . $newValues . '</td>
                    <td>' . $origDueDate . '</td>
                    <td>' . $newDueDate . '</td>
                  </tr>
                    ';
            }
            $html .= '</tbody></table>';
        }

        return $html;
    }

}
