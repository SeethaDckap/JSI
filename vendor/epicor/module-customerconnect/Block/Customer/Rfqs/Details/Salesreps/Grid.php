<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Salesreps;


/**
 * RFQ sales rep grid
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
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

        $this->setId('rfq_salesreps');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('crqd');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $rfq = $this->registry->registry('customer_connect_rfq_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if ($rfq) {
            $salesrepsData = ($rfq->getSalesReps()) ? $rfq->getSalesReps()->getasarraySalesRep() : array();
            $salesreps = array();

            // add a unique id so we have a html array key for these things
            foreach ($salesrepsData as $row) {
                $row->setUniqueId(uniqid());
                $salesreps[] = $row;
            }

            $this->setCustomData($salesreps);
        }
    }

    protected function _getColumns()
    {
        $columns = array();

        $columns['delete'] = array(
            'header' => __('Delete'),
            'align' => 'center',
            'index' => 'delete',
            'type' => 'text',
            'width' => '50px',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Salesreps\Renderer\Delete',
            'filter' => false,
            'column_css_class' => $this->registry->registry('rfqs_editable') ? '' : 'no-display',
            'header_css_class' => $this->registry->registry('rfqs_editable') ? '' : 'no-display',
        );

        $columns['name'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'type' => 'text',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Salesreps\Renderer\Name',
            'filter' => false
        );

        return $columns;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();

        $html .= '<div style="display:none">
            <table>
                <tr title="" class="salesreps_row" id="salesreps_row_template">
                    <td class="a-center">
                        <input type="checkbox" name="salesreps[][delete]" class="salesreps_delete"/>
                    </td>
                    <td class="a-left last">
                        <input type="text" value="" name="salesreps[][name]" class="salesreps_name" />
                    </td>
                </tr>
            </table>
        </div>';
        $html .= '</script>';
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        $extra = $this->registry->registry('rfq_new') ? ' new' : '';
        return 'salesreps_row' . $extra;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
