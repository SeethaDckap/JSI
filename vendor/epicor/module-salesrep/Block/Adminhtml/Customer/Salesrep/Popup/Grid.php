<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Popup;


/**
 * 
 * ERP Account grid for erp account selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory
     */
    protected $salesRepResourceAccountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory $salesRepResourceAccountCollectionFactory,
        array $data = []
    )
    {
        $this->salesRepResourceAccountCollectionFactory = $salesRepResourceAccountCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('salesrepaccount_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setRowClickCallback('accountSelector.selectAccount.bind(accountSelector)');
        $this->setRowInitCallback('accountSelector.updateWrapper.bind(accountSelector)');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->salesRepResourceAccountCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

//        $this->addColumn('erp_code', array(
//            'header' => $this->__('ERP Customer Code'),
//            'index' => 'erp_code',
//            'width' => '20px',
//            'filter_index' => 'erp_code'
//        ));
        $this->addColumn('sales_rep_id', array(
            'header' => __('Sales Rep Id'),
            'index' => 'sales_rep_id',
        ));
//        $this->addColumn('short_code', array(
//            'header' => $this->__('Short Code'),
//            'index' => 'short_code',
//            'width' => '20px',
//            'filter_index' => 'short_code'
//        ));
//        $this->addColumn('account_number', array(
//            'header' => $this->__('Account Number'),
//            'index' => 'account_number',
//            'width' => '20px',
//            'filter_index' => 'account_number'
//        ));
        $this->addColumn('rowdata', array(
            'header' => __(''),
            'align' => 'left',
            'width' => '1',
            'name' => 'rowdata',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        $data = $this->getRequest()->getParams();
        return $this->getUrl('*/*/*', array('grid' => true, 'field_id' => $data['field_id']));
    }

}
