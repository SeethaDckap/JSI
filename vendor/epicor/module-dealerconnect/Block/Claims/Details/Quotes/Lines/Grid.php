<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\Lines;


/**
 * RFQ lines grid
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Grid
{
    
    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerconnectHelper;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Epicor\Dealerconnect\Helper\Data $dealerconnectHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->dealerconnectHelper = $dealerconnectHelper;
        parent::__construct(
        $context,
        $backendHelper,
        $commonMessageCollectionFactory,
        $commonHelper,
        $frameworkHelperDataHelper,
        $registry,
        $commMessagingHelper,
        $catalogProductFactory,
        $dataObjectFactory,
        $localeFormat,
        $customerconnectMessagingHelper,
        $customerconnectHelper,
        $data
        );
    }
    
    protected function _getColumns()
    {
        $columns = array();
        $columns['expand'] = array(
            'header' => __(''),
            'align' => 'left',
            'index' => 'expand',
            'type' => 'text',
            'column_css_class' => "expand-row",
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Expand',
            'filter' => false,
            'sortable' => false
        );

        $columns['is_kit'] = array(
            'header' => __('Kit'),
            'align' => 'left',
            'index' => 'is_kit',
            'type' => 'text',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Iskit',
            'filter' => false,
            'sortable' => false
        );

        $columns['product_code'] = array(
            'header' => __('Part Number'),
            'align' => 'left',
            'index' => 'product_code',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Partnumber',
            'filter' => false,
            'sortable' => false
        );

        $columns['unit_of_measure_code'] = array(
            'header' => __('UOM'),
            'align' => 'left',
            'index' => 'unit_of_measure_code',
            'width' => '50px',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        );

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Description',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        );


        $columns['price'] = array(
            'header' => __('Price'),
            'align' => 'right',
            'index' => 'price',
            'type' => 'number',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency',
            'filter' => false,
            'sortable' => false
        );

        $columns['dealer_price'] = array(
            'header' => __('Price'),
            'align' => 'right',
            'index' => 'dealer_price_inc',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
            'column_css_class' => "no-display",
            'header_css_class' => "no-display"
        );
        
        $columns['quantity'] = array(
            'header' => __('Qty'),
            'align' => 'center',
            'index' => 'quantity',
            'type' => 'number',
            'value_format' => 'int',
            'width' => '60px',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Qty',
            'filter' => false,
            'sortable' => false,
        );

        $columns['rquest_date'] = array(
            'header' => __('Request Date'),
            'align' => 'left',
            'index' => 'request_date',
            'width' => '60px',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Date',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        );

        $columns['additional_text'] = array(
            'header' => __('Line Comments'),
            'align' => 'left',
            'index' => 'additional_text',
            'type' => 'text',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Linecomments',
            'sortable' => false
        );

        if ($this->canShowMisc()){
            $columns['miscellaneous_charges_total'] = array(
                'header' => __('Misc.'),
                'align' => 'left',
                'index' => 'miscellaneous_charges_total',
                'width' => '60px',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency',
                'type' => 'number',
                'filter' => false
            );

            $columns['line_value'] = array(
                'header' => __('Total Price'),
                'align' => 'left',
                'index' => 'misc_line_total',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency',
                'filter' => false,
                'sortable' => false
            );
        }else{
            $columns['line_value'] = array(
                'header' => __('Total Price'),
                'align' => 'left',
                'index' => 'line_value',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency',
                'filter' => false,
                'sortable' => false
            );
        }

        $columns['ecc_return_type'] = array(
            'header' => __('Return Type'),
            'align' => 'left',
            'index' => 'ecc_return_type',
            'type' => 'options',
            'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Quotes\Lines\Renderer\Returntype',
            'filter' => false,
            'sortable' => false
        );

        $columns['_attributes_number'] = array(
            'header' => __('Number'),
            'align' => 'left',
            'index' => '_attributes_number',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
            'column_css_class' => "no-display",
            'header_css_class' => "no-display",
        );

        $columns['select'] = array(
            'header' => __('Select'),
            'align' => 'center',
            'index' => 'delete',
            'type' => 'text',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Select',
            'filter' => false,
            'sortable' => false,
            'column_css_class' => $this->registry->registry('rfqs_editable') ? '' : 'no-display',
            'header_css_class' => $this->registry->registry('rfqs_editable') ? '' : 'no-display',
        );

        $columns['attachments'] = array(
            'header' => __(''),
            'align' => 'left',
            'index' => 'attachments',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\DealerAttachments',
            'type' => 'text',
            'filter' => false,
            'keep_data_format' => 1,
            'column_css_class' => "expand-content",
            'header_css_class' => "expand-content",
            'sortable' => false
        );

        $cols = $this->dataObjectFactory->create(['data' => $columns]);
        $this->eventManager->dispatch('epicor_customerconnect_crq_detail_lines_grid_columns_after', array(
            'block' => $this,
            'columns' => $cols
            )
        );
        return $cols->getData();
    }

    public function _toHtml()
    {
        $html = \Epicor\Common\Block\Generic\Listing\Grid::_toHtml();
        $canShowMisc = $this->canShowMisc();
        $miscDisplay = $canShowMisc ? "" : "display:none";
        $colspan = $canShowMisc ? 13 : 12;
        $checkoutConfig = array(
            'quoteData' => '',
            'basePriceFormat' =>'',
            'priceFormat' =>$this->localeFormat->getPriceFormat(),
            'storeCode' =>'',
            'totalsData' =>'',
        );
        $checkoutConfig = \Zend_Json::encode($checkoutConfig);
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */

        if ($this->registry->registry('current_rfq_row')) {
            $this->registry->unregister('current_rfq_row');
        }

        $this->registry->register('current_rfq_row', $this->dataObjectFactory->create());

        //if (!$block = $this->getLayout()->getBlock('lines.attachments.'.$uniqueId)) {
            $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Claims\Details\Quotes\Lines\Attachments');
        //}
        $attachment_support = $this->scopeConfig->getValue('customerconnect_enabled_messages/CRQD_request/attachment_support', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($attachment_support) {
            $expand = '<td class="a-left expand-row " style="cursor: pointer;"><span id="attachments-" class="plus-minus">+</span>
                     </td>';
        } else {
            $expand = '<td class="" style=><span id="attachments-" class="plus-minus"></span>
                     </td>';
        }
        $eccReturnTypes = $this->dealerconnectHelper->getEccReturnTypeOptions();
        $html .= '<div style="display:none">
            <table>
            <tr title="" class="lines_row" id="lines_row_template">' . $expand . '
                <td class="a-left ">
                    <span class="is_kit_display"></span>
                    <input class="lines_is_kit" type="hidden" name="" value="" />
                    </td>
                <td class="a-left ">
                    <input type="hidden" class="lines_product_code" value="" name="" />
                    <input type="hidden" class="lines_product_type" value="" name="" />
                    <input type="hidden" class="lines_type" value="" name="" />
                    <span class="product_code_display"></span>
                </td>
                <td class="a-left ">
                    <input type="hidden" class="lines_unit_of_measure_code" value="" name="" />
                    <span class="uom_display"></span>
                </td>
                <td class="a-left ">
                    <input type="text" class="lines_description" value="" name="" />
                    <span class="description_display"></span>
                </td>
                <td class="a-left cus-price ">
                    <input type="hidden" class="lines_price" value="" name="" />
                    <span class="lines_price_display"></span>
                </td>
                <td class="a-left dealer-price no-display ">
                    <div class="dealer-container"></div>
                </td>
                <td class="a-center ">
                    <input type="text" class="lines_quantity" value="" name="" />
                </td>
                <td class="a-left ">
                    <input type="text" class="lines_request_date" value="" name="" id="_request_date" />
                </td>
                <td class="a-left ">
                    <textarea class="lines_additional_text"  name=""></textarea>
                </td>
                <td class="a-left" style='.$miscDisplay.'>
                    <input type="hidden" class="lines_miscellaneous_charges_total" value="" name="" />
                    <span class="lines_miscellaneous_charges_total_display">'.__("TBA").'</span>
                </td>
                <td class="a-left ">
                    <input type="hidden" class="lines_line_value" value="" name="" />
                    <span class="lines_line_value_display"></span>
                </td>
                <td class="a-left " style="width:13%;">
                    <span class="ecc_return_type_field">
                        <input type="hidden" class="lines_line_ecc_return_type_field" value="" name="" />
                        <span class="lines_line_ecc_return_type_display"></span>
                    </span>
                    <span class="ecc_return_type_select">
                        <select class="lines_line_ecc_return_type_select" name="">';
                        foreach ($eccReturnTypes as $key => $returnType) {
                            $selected = ($returnType == 'Replace') ? 'selected="selected"' : '';
                            $html .= '<option value="' . $key . '" '. $selected .'>' . $returnType . '</option>';
                        }
        
        $html .=        '</select>
                    </span>
                </td>
                <td class="a-center ">
                    <input type="checkbox" name="" class="lines_select" />
                    <input type="hidden" name="" class="lines_product_json" />
                    <input type="hidden" name="" class="lines_delete" />
                    <input type="hidden" name="" class="lines_orig_quantity" />
                    <input type="hidden" name="" class="lines_uom" />
                    <input type="hidden" name="" class="lines_group_sequence" />
                    <input type="hidden" name="" class="lines_ewa_code" />
                    <input type="hidden" name="" class="lines_ewa_title" />
                    <input type="hidden" name="" class="lines_ewa_sku" />
                    <input type="hidden" name="" class="lines_ewa_short_description" />
                    <input type="hidden" name="" class="lines_ewa_description" />
                    <input type="hidden" name="" class="lines_configured" />
                    <input type="hidden" name="" class="lines_attributes" />
                    <input type="hidden" name="" class="lines_product_id" />
                    <input type="hidden" name="" class="lines_child_id" />
                    <input type="hidden" name="" class="lines_configured" />
                </td>
                <td class="a-left expand-content last"></td>
            </tr>
            <tr class="lines_row attachment" id="line_misc_row_template" style="display:none">
                <td colspan='.$colspan.' class="misc-row">'
            . $block->toHtml()
            . '</td>
            </tr>
            <tr class="lines_row attachment" id="line_attachments_row_template" style="display:none">
                <td colspan='.$colspan.'  class="shipping-row">'
            . $block->toHtml()
            . '</td>
            </tr>
            </table>
        </div>';
        $html .= '</script>';
        $html .= '<script>
        window.checkoutConfig = ' . $checkoutConfig . ';
        </script>';
        return $html;
    }
}
