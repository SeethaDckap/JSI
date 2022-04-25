<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Changes;


/**
 * Supplier Changed order list grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Supplier Connect
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Epicor_Supplier::supplier_confirm_po_changes_confirmrejects';

    private $allowEdit;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionArrayFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $helper = $this->commonAccessHelper;
        $this->allowEdit = $helper->customerHasAccess(
            'Epicor_Supplierconnect',
            'Orders',
            'confirmchanges',
            '',
            'Access'
        );
        $this->setId('supplierconnect_orders_changes');
        $this->setDefaultSort('purchase_order_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('spcs');
        $this->setIdColumn('purchase_order_number');
        $this->initColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT) && $this->allowEdit) {
            $html .= '<div class="">    
                    <button id="purchase_order_confirmreject_save" class="scalable"
                     type="button">Confirm / Reject PO</button>
            </div>';
        }

        return $html;
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        $newColumns = array(
            'expand' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'expand',
                'type' => 'text',
                'column_css_class' => "expand-row",
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Changes\Renderer\Expand',
                'filter' => false
            )
        );

        $columns = array_merge_recursive($newColumns, $columns);

        $columns['lines'] = array(
            'header' => __(''),
            'align' => 'left',
            'index' => 'lines',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Changes\Renderer\Lines',
            'type' => 'text',
            'filter' => false,
            'column_css_class' => "expand-content",
            'header_css_class' => "expand-content",
            'keep_data_format' => 1
        );
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT)){
            $columns= $this->getCustomColumns();
            if(isset($columns['confirm_po_change'])){
                unset($columns['confirm_po_change']);
            }
            if(isset($columns['reject_po_change'])){
                unset($columns['reject_po_change']);
            }
        }

        $this->setCustomColumns($columns);
    }

}
