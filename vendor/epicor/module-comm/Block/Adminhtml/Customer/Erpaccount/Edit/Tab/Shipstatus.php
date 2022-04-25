<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;

/**
 * Erp account Allowed Delivery Methods grid
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Shipstatus extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    protected $_erp_customer;
    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod\CollectionFactory
     */
    protected $commResourceErpMappingShippingstatusCollectionFactory;
    protected $commResourceErpMappingShippingstatus;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Framework\Registry $registry, \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory, \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingstatus\CollectionFactory $commResourceErpMappingShippingstatusCollectionFactory, \Epicor\Comm\Model\Erp\Mapping\Shippingstatus $commResourceErpMappingShippingstatus, array $data = []
    ) {
        $this->registry = $registry;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->commResourceErpMappingShippingstatus = $commResourceErpMappingShippingstatus;
        $this->commResourceErpMappingShippingstatusCollectionFactory = $commResourceErpMappingShippingstatusCollectionFactory;
        parent::__construct(
                $context, $backendHelper, $data
        );
        $this->setId('erpaccount_shipstatus');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('code' => 1));
        $this->setSaveParametersInSession(true);
    }

    public function getErpCustomer() {
        if (!$this->_erp_customer) {
            if ($this->registry->registry('customer_erp_account')) {
                $this->_erp_customer = $this->registry->registry('customer_erp_account');
            } else {
                $this->_erp_customer = $this->commCustomerErpaccountFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->_erp_customer;
    }

    public function canShowTab() {
        return true;
    }

    public function getTabLabel() {
        return 'Shipstatus';
    }

    public function getTabTitle() {
        return 'Shipstatus';
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareCollection() {
        $collection = $this->commResourceErpMappingShippingstatusCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'code') {

            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array();
            }
            $ids2 = $this->commResourceErpMappingShippingstatus->getDefaultErpshipstatus();
            $newIds = array_merge($ids, $ids2);
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('shipping_status_code', array('in' => $newIds));
            } else {
                if ($newIds) {
                    $this->getCollection()->addFieldToFilter('shipping_status_code', array('nin' => $newIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _getSelected() {   // Used in grid to return selected values.
        return array_keys($this->getSelected());
    }

    public function getSelected() {
        $allowed = array();
        $excluded = array();
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $erpAccount = $this->getErpCustomer();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

            $allowed = unserialize($erpAccount->getAllowedShipstatusMethods());
            $excluded = unserialize($erpAccount->getAllowedShipstatusMethodsExclude());
            if (!(empty($allowed) && empty($excluded))) {

                $include = !empty($allowed) ? 'Y' : 'N';
                if ($include == 'Y') {
                    foreach ($allowed as $deliveryCode) {
                        $this->_selected[$deliveryCode] = array('shipping_status_code' => $deliveryCode);
                    }
                } else {
                    foreach ($excluded as $deliveryCode) {
                        $this->_selected[$deliveryCode] = array('shipping_status_code' => $deliveryCode);
                    }
                }
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected) {
        if (!empty($selected)) {
            foreach ($selected as $code) {
                $this->_selected[$code] = array('shipping_status_code' => $code);
            }
        }
    }

    protected function _prepareColumns() {
        $this->addColumn('code', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'code',
            'align' => 'center',
            'index' => 'shipping_status_code',
            'sortable' => false,
            'width' => '20px',
            //'field_name' => 'links[]',
            'values' => $this->_getSelected(),
            'use_index' => true,
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Checkboxgrid',
            'use_index' => true
        ));
        $this->addColumn('shipping_status_code', array(
            'header' => __('Ship Status Code'),
            'align' => 'left',
            'index' => 'shipping_status_code',
        ));

        $this->addColumn('description', array(
            'header' => __('Ship Status Code Description'),
            'align' => 'left',
            'index' => 'description',
        ));
        $this->addColumn('is_default', array(
            'header' => __('Default'),
            'width' => '20px',
            'type' => 'checkbox',
            'align' => 'center',
            'index' => 'is_default',
            'filter' => false,
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Checkbox',
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

    public function getGridUrl() {
        $params = array(
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/shipstatusgrid', $params);
    }

}
