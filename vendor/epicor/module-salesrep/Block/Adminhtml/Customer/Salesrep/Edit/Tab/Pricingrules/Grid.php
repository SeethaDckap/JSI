<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab\Pricingrules;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory
     */
    protected $salesRepResourcePricingRuleCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;


    private $_salesrep;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory $salesRepResourcePricingRuleCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->salesRepResourcePricingRuleCollectionFactory = $salesRepResourcePricingRuleCollectionFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('pricing_rules');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setRowInitCallback('pricingRules.rowInit.bind(pricingRules)');
        $this->setDefaultSort('priority');
        $this->setDefaultDir('desc');
    }

    /**
     *
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getSalesRepAccount()
    {
        if (!$this->_salesrep) {
            $this->_salesrep = $this->registry->registry('salesrep_account');
        }

        return $this->_salesrep;
    }

    protected function _prepareCollection()
    {
        $collection = $this->salesRepResourcePricingRuleCollectionFactory->create();
        /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\Collection */
        $salesRepAccount = $this->getSalesRepAccount();
        $collection->addFieldToFilter('sales_rep_account_id', $salesRepAccount->getId());
        $this->setCollection($collection);
//        echo '<pre>';
//        foreach($collection->getItems() as $item) {
//            var_dump($item->getData());
//        }
//        echo '</pre>';
        return parent::_prepareCollection();
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(array(
                'label' => __('Add'),
                'onclick' => 'window.pricingRules.add()',
                'class' => 'task'
            ))
        );
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header' => __('ID'),
            'width' => '50',
            'index' => 'id',
            'filter_index' => 'id'
        ));

        $this->addColumn('rule_name', array(
            'header' => __('Name'),
            'index' => 'name',
            'filter_index' => 'name'
        ));
        
        $this->addColumn('from_date', array(
            'type' => 'date',
            'header' => __('Date Start'),
            'index' => 'from_date',
            'filter_index' => 'from_date',
            'renderer' => 'Epicor\SalesRep\Block\Widget\Grid\Column\Renderer\Date'
        ));

        $this->addColumn('to_date', array(
            'type' => 'date',
            'header' => __('Date Expire'),
            'index' => 'to_date',
            'filter_index' => 'to_date',
            'renderer' => 'Epicor\SalesRep\Block\Widget\Grid\Column\Renderer\Date'
        ));
        
        $this->addColumn('is_active', array(
            'header' => __('Status'),
            'index' => 'is_active',
            'filter_index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => __('Active'),
                0 => __('Inactive')
            ),
        ));

        $this->addColumn('priority', array(
            'type' => 'number',
            'header' => __('Priority Order'),
            'index' => 'priority',
            'filter_index' => 'priority'
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => 'adminhtml/epicorsalesrep_customer_salesrep/deletepricingrule'),
                    'field' => 'id',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));

        $this->addColumn('conditions', array(
            'header' => __('Conditions'),
            'name' => 'conditions',
            'renderer' => 'Epicor\SalesRep\Block\Adminhtml\Widget\Grid\Column\Renderer\Conditions',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));

        $this->addColumn('rowdata', array(
            'header' => __('Row'),
            'align' => 'left',
            'name' => 'rowdata',
            'width' => 0,
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));

        return parent::_prepareColumns();
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();

            if ($this->getSalesRepAccount()->getId()) {
                $collection->addFieldToFilter('salesrep_id', $this->getSalesRepAccount()->getId());
            }

            /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
            foreach ($collection->getAllIds() as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getSalesRepAccount()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorsalesrep_customer_salesrep/pricingrulesgrid', $params);
    }

    public function getRowUrl($row)
    {
        return "javascript:pricingRules.rowEdit(this, " . $row->getId() . ");";
    }

    protected function _toHtml()
    {
        if (!$this->getSalesRepAccount() || !$this->getSalesRepAccount()->getId()) {
            return '<div id="messages"><ul class="messages"><li class="warning-msg"><ul><li><span>' . __('Pricing rules can not be created until you have saved the sales rep for the first time') . '</span></li></ul></li></ul></div>';
        } else {
            return parent::_toHtml();
        }
    }

}
