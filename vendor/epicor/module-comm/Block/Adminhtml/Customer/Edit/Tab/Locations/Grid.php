<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Edit\Tab\Locations;

use Magento\Customer\Controller\RegistryConstants;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    protected $_customerid;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->customer = $customer;
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }
    /**
     * @return void
     */
    protected function _construct()
    {
      
        $this->setId('locationsGrid');
         $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultFilter(array('in_location' => 1));
        
        /*
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('locations_filter');
         */
        parent::_construct();
    }

    
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
         if ($column->getId() == 'in_location') {

            $productIds = $this->_getSelected();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('code', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('code', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomerId()
    {
        $customerId = (int)$this->getRequest()->getParam('id');
        if (!$this->_customerid) {
            $this->_customerid = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
            $this->_customerid = $this->_customerid ?: (int)$this->getRequest()->getParam('id');
        }
        return $this->_customerid;
        
    }

    protected function _prepareCollection() {
        $collection = $this->commResourceLocationCollectionFactory->create();
        $customerId = $this->getCustomerId();
        $customer = $this->customer->load($customerId);
        if ($customer->getEccLocationLinkType() == "customer") {
            $allowed = $customer->getAllowedLocationCodes();
        } else {
            $allowed = $customer->getCustomerErpAccount()->getAllowedLocationCodes();
        }
        $_company = $customer->getStore()->getWebsite()->getEccCompany() ?: $customer->getStore()->getGroup()->getEccCompany();
        $collection->addFieldToFilter('code', array('in' => $allowed));
        if ($_company) {
            $collection->addFieldToFilter('company', $_company);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {  
         
         $this->addColumn('in_location', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_location',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'code',
            'data-form-part' => $this->getData('target_form'),
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true,
            'column_css_class' => 'location_set',
            'header_css_class' => 'location_set'
        ));

        $this->addColumn('location_code', array(
            'header' => __('Location Code'),
            'width' => '150',
            'index' => 'code',
            'filter_index' => 'code'
        ));

        $this->addColumn('location_name', array(
            'header' => __('Name'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
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
        return array_map('strval',array_keys($this->getSelected()));
    }

    public function getSelected($json = false) {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $customerId = $this->getCustomerId();
            $customer = $this->customer->load($customerId);
            if ($customer->getEccLocationLinkType() == "customer") {
                $locations = $customer->getAllowedLocationCodes();
            } else {
                $locations = $customer->getCustomerErpAccount()->getAllowedLocationCodes();
            }
            //$locations = $customer->getAllowedLocationCodes();
            foreach ($locations as $locationCode) {
                $this->_selected[$locationCode] = array('code' => $locationCode);
            }
        }
        if ($json) {
            $customerId = $this->getCustomerId();
            $customer = $this->customer->load($customerId);

            if ($customer->getEccLocationLinkType() == "customer") {
                $locations = $customer->getAllowedLocationCodes();
            } else {
                $locations = $customer->getCustomerErpAccount()->getAllowedLocationCodes();
            } $jsonLists = [];
            foreach ($locations as $locationCode) {
                $jsonLists[$locationCode] = 0;
            }
            return $this->_jsonEncoder->encode($jsonLists);
        }
        return $this->_selected;
    }

    public function setCustomerId($customerid)
    {
        if ($customerid) {
            $this->_customerid = $customerid;
        }
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
          //'customer_id' =>  $this->getCustomerId(),
           'id' =>  $this->getCustomerId(),
            '_current' => true,
        );
        //return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/locationsgrid', $params);
        return $this->getUrl('adminhtml/epicorcomm_customer/locationsgrid', $params);
    }

}
