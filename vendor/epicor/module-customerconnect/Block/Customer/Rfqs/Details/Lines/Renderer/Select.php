<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ line delete column renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commProductHelper = $commProductHelper;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $helper = $this->customerconnectHelper;

        $atts = array();
        
        if ($this->registry->registry('rfqs_editable')) {
            $html = '<input type="checkbox" class="lines_select" name="lines[' . $key . '][' . $row->getUniqueId() . '][select]" />';

            $atts = $this->_getRowAttributes($row);
            $productJson = $this->_getRowProductJson($row);
            $sku = (string) $row->getData('product_code');
            $rowProduct = $this->customerconnectMessagingHelper->getProductObject($sku);
            $html .= '<input class="lines_product_json" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][lines_product_json]" value="' . $productJson . '" /> ';
            $html .= '<input class="lines_product_id" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][lines_product_id]" value="' . $rowProduct->getId() . '" /> ';
            $html .= '<input class="lines_child_id" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][lines_child_id]" value="" /> ';
        } else {
            $sku = (string) $row->getData('product_code');
            $rowProduct = $this->customerconnectMessagingHelper->getProductObject($sku);
            $html = '';
        }

        $html .= '<input type="hidden" class="lines_delete" name="lines[' . $key . '][' . $row->getUniqueId() . '][delete]" />';
        if (!$this->registry->registry('rfq_new')) {
            $oldDetails = base64_encode(serialize($helper->varienToArray($row)));
            $html .= '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][old_data]" value="' . $oldDetails . '" /> ';
        }
        $html .= '<input class="lines_configured" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][configured]" value="" /> ';
        $html .= '<input class="lines_tax_code" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][tax_code]" value="' . $row->getData('tax_code') . '" /> ';
        $html .= '<input class="lines_product_code" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][product_code]" value="' . $row->getData('product_code') . '" /> ';
        $html .= '<input class="lines_product_type" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][ecc_product_type]" value="' . $rowProduct->getEccProductType() . '" /> ';
        $html .= '<input class="lines_type" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][type]" value="' . $row->getData('product_code__attributes_type') . '" /> ';
        $html .= '<input class="lines_orig_quantity" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][origqty]" value="' . $row->getData('quantity') . '" /> ';
        $html .= '<input class="lines_sku" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][skuref]" value="' . $row->getData('product_code') . '" /> ';
        $html .= '<input class="lines_uom" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][uomref]" value="' . $row->getData('unit_of_measure_code') . '" /> ';
        $html .= '<input class="lines_unit_of_measure_code" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][unit_of_measure_code]" value="' . $row->getData('unit_of_measure_code') . '" /> ';
        $html .= '<input class="lines_group_sequence" id="group_sequence_' . $row->getUniqueId() . '" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][group_sequence]" value="' . $row->getData('group_sequence') . '" /> ';
        $html .= '<input class="lines_ewa_code" id="ewa_code_' . $row->getUniqueId() . '" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][ewa_code]" value="' . $row->getEwaCode() . '" /> ';

        $html .= '<input class="lines_ewa_title" id="ewa_title_' . $row->getUniqueId() . '" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][ewa_title]" value="' . $row->getEwaTitle() . '" /> ';
        $html .= '<input class="lines_ewa_sku" id="ewa_sku_' . $row->getUniqueId() . '" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][ewa_sku]" value="' . $row->getEwaSku() . '" /> ';
        $html .= '<input class="lines_ewa_short_description" id="ewa_short_description_' . $row->getUniqueId() . '" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][ewa_code]" value="' . $row->getEwaShortDescription() . '" /> ';
        $html .= '<input class="lines_ewa_description" id="ewa_ewa_description_' . $row->getUniqueId() . '" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][ewa_code]" value="' . $row->getEwaDescription() . '" /> ';

        $html .= '<input class="lines_attributes" type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][attributes]" value="' . htmlentities(base64_encode(serialize($atts))) . '" /> ';

        return $html;
    }

    protected function _getRowAttributes($row)
    {
        $groupSequence = $row->getData('group_sequence');
        $attributes = $row->getData('attributes');
        $atts = array();
        if ($attributes) {
            $attData = $attributes->getasarrayAttribute();

            foreach ($attData as $att) {
                if ($att->getDescription() == 'groupSequence') {
                    $groupSequence = $att->getValue();
                }
                $atts[] = array(
                    'description' => $att->getDescription(),
                    'value' => $att->getValue(),
                );
            }
        } else if (!empty($groupSequence)) {
            $atts[] = array(
                'description' => 'groupSequence',
                'value' => $groupSequence,
            );
        }

        $row->setGroupSequence($groupSequence);

        return $atts;
    }

    protected function _getRowProductJson($row)
    {
        $helper = $this->customerconnectHelper;
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $currency = $helper->getCurrencyMapping($rfq->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $rowPrice = $row->getPrice();
        $rowQty = $row->getQuantity();

        $productHelper = $this->commProductHelper;
        $sku = (string) $row->getData('product_code');
        $rowProduct = $this->customerconnectMessagingHelper->getProductObject($sku);
        if($rowProduct->getTypeId() === 'grouped' ){
            $uom = $row->getData('unit_of_measure_code');
            $uomSeparator = $productHelper->getUOMSeparator();
            $sku = $sku.$uomSeparator.$uom;
            $rowProduct = $this->customerconnectMessagingHelper->getProductObject($sku);
        }
        if ($rowPrice == 'TBC') {
            $formattedPrice = __('TBC');
            $formattedTotal = __('TBC');
        } else {
            $formattedPrice = $helper->formatPrice($rowPrice, true, $currency);
            $formattedTotal = $helper->formatPrice($rowPrice * $rowQty, true, $currency);
        }
        if($rowProduct instanceof \Epicor\Comm\Model\Product){
            $rowProduct->setIsCustom($rowProduct->isObjectNew());
        }else{
            $rowProduct->setSku($sku);
            $rowProduct->setIsCustom(1);
        }
        $rowProduct->setUsePrice($rowPrice);
        $rowProduct->setMsqFormattedPrice($formattedPrice);
        $rowProduct->setMsqFormattedTotal($formattedTotal);
        $rowProduct->setMsqQty($rowQty);

        $productInfo = $productHelper->getProductInfoArray($rowProduct);
        $productInfo['request_date'] = $rowProduct->getRequestDate();
        $eccReturnType = $rowProduct->getAttributeText('ecc_return_type');
        $productInfo['ecc_return_type'] = '';
        $productInfo['ecc_return_type_display'] = $eccReturnType;
        switch (true) {
            case ($eccReturnType == 'Credit'):
                $productInfo['ecc_return_type'] = 'C';
                break;
            case ($eccReturnType == 'Replace'):
                $productInfo['ecc_return_type'] = 'S';
                break;
        }
        return htmlentities(json_encode($productInfo));
    }

}
