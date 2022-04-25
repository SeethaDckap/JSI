<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Create;


/**
 * Supplier Parts list Grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Supplier Connect
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Epicor_Supplier::supplier_confirm_new_po_confirmrejects';

    protected $_configLocation = 'newpogrid_config';
    private $_allowEdit;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionArrayFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->supplierconnectHelper = $supplierconnectHelper;
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
        $this->_allowEdit = $helper->customerHasAccess('Epicor_Supplierconnect', 'Orders', 'confirmnew', '', 'Access');

        $this->setId('supplierconnect_orders_create');
        $this->setDefaultSort('purchase_order_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('spos');
        $this->setIdColumn('purchase_order_number');
        $this->initColumns();

        $filter1 = $this->dataObjectFactory->create();
        $filter1->addData(array(
            'field' => 'confirm_via',
            'value' => array('eq' => 'NEW')
        ));
        $filter2 = $this->dataObjectFactory->create();
        $filter2->addData(array(
            'field' => 'order_confirmed',
            'force' => true,
            'type' => 'text',
            'value' => array('eq' => 'NC'),
        ));
        $filter3 = $this->dataObjectFactory->create();
        $filter3->addData(array(
            'field' => 'order_status',
            'force' => true,
            'type' => 'text',
            'value' => array('eq' => 'O'),
        ));

        $this->setAdditionalFilters([$filter1, $filter2, $filter3]);
    }

    protected function initColumns()
    {
        parent::initColumns();

        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT)){
            $columns= $this->getCustomColumns();
            if(isset($columns['new_po_confirm'])){
                unset($columns['new_po_confirm']);
            }
            if(isset($columns['new_po_reject'])){
                unset($columns['new_po_reject']);
            }
            $columnObject = $this->dataObjectFactory->create(['data' => $columns]);
            $this->setCustomColumns($columnObject->getData());
        }
    }

    public function getRowUrl($row)
    {
        return false;
//
//        $helper = $this->supplierconnectHelper;
//        $erp_account_number = $helper->getSupplierAccountNumber();
//        $requested = $helper->urlEncode($helper->encrypt($erp_account_number . ']:[' . $row->getId()));
//        return $this->getUrl('*/*/details', array('order' => $requested));
//
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT) && $this->_allowEdit) {
            $html .= '<div class="">    
                 <button id="purchase_order_confirmreject_save" class="scalable" 
                 type="button">Confirm / Reject PO</button>
            </div>';
        }

        return $html;
    }

}
