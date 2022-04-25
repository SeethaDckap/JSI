<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/**
 * Erp account Allowed Payment Methods grid
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Payments extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
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
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment\CollectionFactory
     */
    protected $commResourceErpMappingPaymentCollectionFactory;
   
    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Paymentmethods
     */
    protected $paymentMethods;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment\CollectionFactory $commResourceErpMappingPaymentCollectionFactory,
        \Epicor\Comm\Model\Erp\Mapping\Paymentmethods $paymentMethods,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->commResourceErpMappingPaymentCollectionFactory = $commResourceErpMappingPaymentCollectionFactory;
        $this->paymentMethods=$paymentMethods;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpaccount_payments');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('code' => 1));
        $this->setSaveParametersInSession(true);
    }

    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            if ($this->registry->registry('customer_erp_account')) {
                $this->_erp_customer = $this->registry->registry('customer_erp_account');
            } else {
                $this->_erp_customer = $this->commCustomerErpaccountFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->_erp_customer;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Payments';
    }

    public function getTabTitle()
    {
        return 'Payments';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceErpMappingPaymentCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        $allowed = array();
        $excluded = array();
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $erpAccount = $this->getErpCustomer();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

            $allowed = unserialize($erpAccount->getAllowedPaymentMethods());
            $excluded = unserialize($erpAccount->getAllowedPaymentMethodsExclude());

            if (!(empty($allowed) && empty($excluded))) {

                $include = !empty($allowed) ? 'Y' : 'N';
                if ($include == 'Y') {
                    foreach ($allowed as $paymentCode) {
                        $this->_selected[$paymentCode] = array('magento_code' => $paymentCode);
                    }
                } else {
                    foreach ($excluded as $paymentCode) {
                        $this->_selected[$paymentCode] = array('magento_code' => $paymentCode);
                    }
                }
            }
        }

        return $this->_selected;
    }

    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $code) {
                $this->_selected[$code] = array('magento_code' => $code);
            }
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'code') {

            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array();
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('magento_code', array('in' => $ids));
            } else {
                if ($ids) {
                    $this->getCollection()->addFieldToFilter('magento_code', array('nin' => $ids));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'code',
            'align' => 'center',
            'index' => 'magento_code',
            'sortable' => false,
            'field_name' => 'links[]',
            'values' => $this->_getSelected(),
            'use_index' => true
        ));
        $this->addColumn('magento_code', array(
            'header' => __('Payment Method'),
            'align' => 'left',
            'index' => 'magento_code',
            'width' => '50%',
            'type' => 'options',
            'options' => $this->paymentMethods->getPaymentMethodList(true),
            'option_groups' => $this->paymentMethods->getPaymentMethodList(true, true, true),
        ));


        $this->addColumn('payment_code', array(
            'header' => __('ERP Code'),
            'align' => 'left',
            'index' => 'erp_code',
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

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/paymentsgrid', $params);
    }

}
