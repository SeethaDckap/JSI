<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/**
 * Erp account master shoppers grid
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Mastershopper extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_erp_customer;
    private $_selected = array();

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Store\Model\System\Store $storeSystemStore,
        array $data = []
    )
    {
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->registry = $registry;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('masterShopper');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultFilter(array('is_master' => 1));
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addFieldToFilter('ecc_erpaccount_id', $this->getErpCustomer()->getId());
            $collection->addAttributeToFilter('ecc_master_shopper', 1);
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
        return 'Master Shoppers';
    }

    public function getTabTitle()
    {
        return 'Master Shoppers';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addNameToSelect();
        $collection->addFieldToFilter('ecc_erpaccount_id', $this->getErpCustomer()->getId());
        $collection->addAttributeToSelect('ecc_master_shopper');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'is_master') {

            $productIds = $this->_getSelected();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('is_master', array(
            'header' => __('Select'),
            'align' => 'left',
            'type' => 'checkbox',
            'index' => 'entity_id',
            'name' => 'is_master',
            'values' => $this->_getSelected(),
            'sortable' => false,
            'field_name' => 'links[]',
        ));

        $this->addColumn('name', array(
            'header' => __('Customer'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'name',
        ));

        $this->addColumn('email', array(
            'header' => __('Email'),
            'align' => 'left',
            'index' => 'email',
            'filter_index' => 'email'
        ));
        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
        //M1 > M2 Translation End
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'left',
                'type' => 'options',
                'index' => 'website_id',
                'options' => $this->storeSystemStore->getWebsiteOptionHash(true),
            ));
        }


        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'ecc_master_shopper',
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
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/mastershoppergrid', $params);
    }

}
