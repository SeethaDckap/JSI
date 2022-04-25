<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('rfq_price_breaks');
        $this->setDefaultSort('manufacturer');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('surd');
        $this->setIdColumn('price_break_code');
        $this->setDataSubset('price_breaks/price_break');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        /* @var $rfq Epicor_Common_Model_Xmlvarien */
        if($rfq) {
            $this->setCustomData((array)$rfq->getVarienDataArrayFromPath('price_breaks/price_break'));
        }
    }

    protected function _getColumns()
    {

        $columns = array();

        if ($this->registry->registry('rfq_editable')) {
            $columns['delete_option'] = array(
                'header' => __("Delete"),
                'align' => 'center',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer\Delete',
                'type' => 'checkbox',
                'sortable' => false,
                'width' => '40'
            );
        }

        $columns['quantity'] = array(
            'header' => __("Minimum Qty"),
            'align' => 'left',
            'index' => 'quantity',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer\Quantity',
            'type' => 'input',
            'sortable' => false
        );

        $columns['days_out'] = array(
            'header' => __("Days Out"),
            'align' => 'left',
            'index' => 'days_out',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer\Daysout',
            'type' => 'input',
            'sortable' => false
        );

        $columns['modifier'] = array(
            'header' => __("Modifier"),
            'align' => 'left',
            'index' => 'modifier',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer\Modifier',
            'type' => 'input',
            'sortable' => false
        );

        $columns['effective_price'] = array(
            'header' => __("Effective Price"),
            'align' => 'left',
            'index' => 'effective_price',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer\EffectivePrice',
            'type' => 'input',
            'sortable' => false
        );


        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '<div style="display:none">
            <table>
            <tr title="" class="qpb_row" id="price_break_row_template">
                <td class="a-center">
                    <input type="checkbox" value="1" name="template_price_breaks[][delete]" class="price_break_delete"/>
                </td>
                <td class="a-left">
                    <input type="text" value="" name="template_price_breaks[][quantity]" class="price_break_min_quantity"/>
                </td>
                <td class="a-left">
                    <input type="text" value="" name="template_price_breaks[][days_out]" class="price_break_days_out"/>
                </td>
                <td class="a-left ">
                    <input type="text" value="" name="template_price_breaks[][modifier]" class="price_break_modifier"/>
                </td>
                <td class="a-left last">
                    <input type="hidden" value="" name="template_price_breaks[][effective_price]" class="price_break_effective_price"/>
                    <span class="price_break_effective_price_label"></span>
                </td>
            </tr> 
            </table>
        </div>';
        $html .= '</script>';
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        return 'qpb_row';
    }

}
