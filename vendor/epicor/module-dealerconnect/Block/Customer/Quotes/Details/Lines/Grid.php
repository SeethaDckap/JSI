<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines;


/**
 * RFQ lines grid
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{
    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_orders_misc';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

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
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->eventManager = $context->getEventManager();
        $this->scopeConfig = $context->getScopeConfig();
        $this->localeFormat = $localeFormat;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->commHelper = $commonHelper->getCommHelper();
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('rfq_lines');
        $this->setDefaultSort('_attributes_number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('crqd');
        $this->setIdColumn('product_code');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $this->getLineData();
    }

    protected function getLineData()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if ($rfq) {
            $linesData = ($rfq->getLines()) ? $rfq->getLines()->getasarrayLine() : array();

            $lines = array();

            $editable = $this->registry->registry('rfqs_editable');
            $helper = $this->commMessagingHelper;

            // add a unique id so we have a html array key for these things
            foreach ($linesData as $row) {
                $row->setUniqueId(uniqid());
                $row->setRowIdentifier('lines_' . $row->getUniqueId());
                $eccReturnType = $row->getData('_attributes')->getReturnType() ? $row->getData('_attributes')->getReturnType() : '';
                $row->setEccReturnType($eccReturnType);
                $sku = (string)$row->getData('product_code');
                $product = $this->customerconnectMessagingHelper->getProductObject($sku);
                $productReturnType = $product->getAttributeText('ecc_return_type');
                $row->setProductReturnType($productReturnType);
                if ($editable && empty($product)) {
                    $productCode = (string)$row->getData('product_code');
                    $productUom = $row->getData('unit_of_measure_code');
                    $rowProduct = $helper->findProductBySku($productCode, $productUom, false);
                    if (empty($rowProduct) || !$rowProduct instanceof \Epicor\Comm\Model\Product) {
                        $rowProduct = $this->catalogProductFactory->create();
                        $rowProduct->setSku($productCode);
                        $rowProduct->setEccUom($productUom);
                    }

                    $rowProduct->setQty($row->getQuantity());
                    $rowProduct->setRfqLineId($row->getUniqueId());
                    unset($rowProduct['extension_attributes']);
                    $row->setProduct($rowProduct);
                }

                $lines[] = $row;
            }

            $lineData = $this->dataObjectFactory->create(['data' => $lines]);
            $this->eventManager->dispatch('epicor_customerconnect_crq_detail_lines_get_data_after', array(
                    'block' => $this,
                    'lines' => $lineData,
                )
            );

            $this->setCustomData($lineData->getData());
        }
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
            'align' => 'left',
            'index' => 'price',
            'type' => 'number',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency',
            'filter' => false,
            'sortable' => false
        );

        $columns['dealer_price'] = array(
            'header' => __('Customer Price'),
            'align' => 'left',
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
        }

        $columns['line_value'] = array(
            'header' => __('Total Price'),
            'align' => 'left',
            'index' => 'line_value',
            'type' => 'number',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency',
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
            'renderer' => 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Renderer\Attachments',
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
        $html = parent::_toHtml();
        $canShowMisc = $this->customerconnectHelper->showMiscCharges();
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
        $hidePrices = $this->commHelper->getEccHidePrice();
        $priceStyle = ($hidePrices && in_array($hidePrices, [1,2,3])) ? 'hide_prices' : '';

        //if (!$block = $this->getLayout()->getBlock('lines.attachments.'.$uniqueId)) {
            $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Attachments');
        //}
        $attachment_support = $this->scopeConfig->getValue('customerconnect_enabled_messages/CRQD_request/attachment_support', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($attachment_support) {
            $expand = '<td class="a-left expand-row " style="cursor: pointer;"><span id="attachments-" class="plus-minus">+</span>
                     </td>';
        } else {
            $expand = '<td class="" style=><span id="attachments-" class="plus-minus"></span>
                     </td>';
        }
        $html .= '<div style="display:none">
            <table>
            <tr title="" class="lines_row" id="lines_row_template">' . $expand . '
                <td class="a-left ">
                    <span class="is_kit_display"></span>
                    <input class="lines_is_kit" type="hidden" name="" value="" />
                    </td>
                <td class="a-left ">
                    <input type="hidden" class="lines_product_code" value="" name="" />
                    <input type="hidden" class="lines_type" value="" name="" />
                    <input type="hidden" class="lines_product_type" value="" name="" />
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
                <td class="a-left cus-price '.$priceStyle.'">
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
                <td class="a-left" >
                    <textarea class="lines_additional_text"  name=""></textarea>
                </td>
                <td class="a-left" style='.$miscDisplay.'>
                    <input type="hidden" class="lines_miscellaneous_charges_total" value="" name="" />
                    <span class="lines_miscellaneous_charges_total_display">'.__("TBA").'</span>
                </td>
                <td class="a-left '.$priceStyle.'">
                    <input type="hidden" class="lines_line_value" value="" name="" />
                    <span class="lines_line_value_display"></span>
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
                <td colspan='.$colspan.' class="shipping-row">'
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

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        $extra = $this->registry->registry('rfq_new') ? ' new' : '';
        return 'lines_row' . $extra;
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER);
        return $showMiscCharges && $isMiscAllowed;
    }
}
