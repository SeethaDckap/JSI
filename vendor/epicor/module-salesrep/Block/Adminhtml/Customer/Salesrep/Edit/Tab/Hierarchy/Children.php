<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab\Hierarchy;


class Children extends \Magento\Backend\Block\Widget\Grid\Extended  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory
     */
    protected $salesRepResourceAccountCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Hierarchy\CollectionFactory
     */
    protected $salesRepResourceHierarchyCollectionFactory;

    private $_salesRepAccount;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory $salesRepResourceAccountCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\SalesRep\Model\ResourceModel\Hierarchy\CollectionFactory $salesRepResourceHierarchyCollectionFactory,
        array $data = []
    )
    {
        $this->salesRepResourceAccountCollectionFactory = $salesRepResourceAccountCollectionFactory;
        $this->registry = $registry;
        $this->salesRepResourceHierarchyCollectionFactory = $salesRepResourceHierarchyCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('childrenGrid');
        $this->setGridHeader(__('Children'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultFilter(array('selected_children' => 1));
    }

    protected function _prepareCollection()
    {
        $collection = $this->salesRepResourceAccountCollectionFactory->create()->addFieldToFilter('id', array('neq' => $this->getSalesRepAccount()->getId()));

        /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Account\Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in salesreps flag
        if ($column->getId() == 'selected_children') {
            $salesrepIds = $this->_getSelected();

            if (empty($salesrepIds)) {
                $salesrepIds = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', array('in' => $salesrepIds));
            } else {
                if ($salesrepIds) {
                    $this->getCollection()->addFieldToFilter('id', array('nin' => $salesrepIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Parents';
    }

    public function getTabTitle()
    {
        return 'Parents';
    }

    public function isHidden()
    {
        return false;
    }

    public function getSalesRepAccount()
    {
        if (!$this->_salesRepAccount) {
            $this->_salesRepAccount = $this->registry->registry('salesrep_account');
        }
        return $this->_salesRepAccount;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_children', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_salesreps',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'id',
            'filter_index' => 'id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));

        $this->addColumn('child_sales_rep_id', array(
            'header' => __('Sales Rep Account Number'),
            'align' => 'left',
            'index' => 'sales_rep_id',
            'filter_index' => 'sales_rep_id',
        ));

        $this->addColumn('child_name', array(
            'header' =>  __('Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'name',
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
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

            $collection = $this->salesRepResourceHierarchyCollectionFactory->create()->addFieldToFilter('parent_sales_rep_account_id', $this->getSalesRepAccount()->getId());
            /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Erpaccount\Collection */

            foreach ($collection as $salesRep) {
                $this->_selected[$salesRep->getChildSalesRepAccountId()] = array('id' => $salesRep->getChildSalesRepAccountId());
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
        return $this->getUrl('adminhtml/epicorsalesrep_customer_salesrep/childrengrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }
    public function _toHtml()
    {
       $html = parent::_toHtml();
       $html .= '<script>
                require([
                    "jquery"
                ],  function($){
                    //<![CDATA[
                        $(document).ready(function() {
                           jQuery("#parentsGrid").before(\'<div class="fieldset-wrapper-title"><strong class="title"><span>Parents</span></strong></div>\');
                    //]]>
                    })
                }) 
                </script>';
       return $html;
    }

}
