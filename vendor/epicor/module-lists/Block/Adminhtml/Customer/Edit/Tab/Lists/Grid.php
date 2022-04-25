<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Customer\Edit\Tab\Lists;

use Magento\Customer\Controller\RegistryConstants;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{


    const LIST_STATUS_ACTIVE = 'A';
    const LIST_STATUS_DISABLED = 'D';
    const LIST_STATUS_ENDED = 'E';
    const LIST_STATUS_PENDING = 'P';
    
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
    protected $_customerModel;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
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
      
        $this->setId('custlistslistGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('custlists_filter');
        parent::_construct();
    }

    
    
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_custlist') {

            $productIds = $this->_getSelected();
            if (empty($productIds)) {
                $productIds = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('id', array('nin' => $productIds));
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
    public function getCustomer()
    {
        if (!$this->_customerModel) {
            $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
            $this->_customerModel = $this->customerCustomerFactory->create()->load($customerId);
        }
        return $this->_customerModel;
    }

    
    protected function _prepareCollection()
    {
        $customerDetails = $this->getCustomer();
        $erpAccountId = $customerDetails->getEccErpaccountId();
        $account = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        $erpAccountType = $account->getAccountType();
        $customerAccountType = $customerDetails->getEccErpAccountType();
        $guestSaleRep = array('guest', 'salesrep');
        if (in_array($customerAccountType, $guestSaleRep)) {
            $typeFilter = $customerAccountType == 'salesrep' ? array('B', 'C', 'E', 'N') : array('E', 'N');
        } else {
            $typeFilter = $erpAccountType == 'B2B' ? array('B', 'E', 'N') : array('C', 'E', 'N');
        }
        $collection = $this->listsResourceListModelCollectionFactory->create();
        $collection->addFilterToMap('id', 'main_table.id');
        $collection->getSelect()->joinLeft(
            array('lea' => $collection->getTable('ecc_list_erp_account')), 'lea.list_id=main_table.id', array('lea.erp_account_id')
        );
        $collection->addFieldToFilter('main_table.erp_account_link_type', array('in' => $typeFilter));
        //if the customer account type is customer(not guest/salesrep) 
        // need to show lists that have a link type of "B2B" / "B2C" that have no erp accounts
        // if the customer's ERP Account matches that type
        if (!in_array($customerAccountType, $guestSaleRep)) {
            $collection->addFieldToFilter(array('lea.erp_account_id', 'lea.erp_account_id'), array(array('eq' => $erpAccountId), array('null' => 'true')));
        } else if ($customerAccountType == "guest") { // if the customer account type is guest then check in N and E(that have no erp accounts)
            $collection->addFieldToFilter(array('lea.erp_account_id'), array(array('null' => 'true')));
        }
        $collection->addFieldToFilter('main_table.type', array('neq' => 'Co'));
        $collection->getSelect()->group('main_table.id');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }


    

    protected function _prepareColumns()
    {
        $this->addColumn('in_custlist', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_custlist',
            'align' => 'center',
            'index' => 'id',
            'sortable' => false,
            'field_name' => 'links[]',
            'values' => $this->_getSelected(),
        ));
        $typeModel = $this->listsListModelTypeFactory->create();
        $this->addColumn('type', array(
            'header' => __('Type'),
            'width' => '150',
            'index' => 'type',
            'type' => 'options',
            'options' => $typeModel->toFilterArray()
        ));

        $this->addColumn('title', array(
            'header' => __('Title'),
            'width' => '150',
            'index' => 'title',
            'filter_index' => 'title'
        ));

        $this->addColumn('lists_erp_code', array(
            'header' => __('Erp Code'),
            'width' => '150',
            'index' => 'erp_code',
            'filter_index' => 'erp_code'
        ));
        $this->addColumn(
            'active', array(
            'header' => __('Current Status'),
            'index' => 'active',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'type' => 'options',
            'filter_condition_callback' => array($this, '_statusFilter'),
            'renderer' => '\Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Active',
            'options' => array(
                self::LIST_STATUS_ACTIVE => __('Active'),
                self::LIST_STATUS_DISABLED => __('Disabled'),
                self::LIST_STATUS_ENDED => __('Ended'),
                self::LIST_STATUS_PENDING => __('Pending')
            ),
            )
        );

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

    public function _statusFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        switch ($value) {
            case self::LIST_STATUS_ACTIVE:
                $collection->addFieldToFilter('active', 1);
                $collection->addFieldToFilter('start_date', array(
                    //M1 > M2 Translation Begin (Rule 25)
                    //array('lteq' => now()),
                    array('lteq' => date('Y-m-d H:i:s')),
                    //M1 > M2 Translation End
                    array('null' => 1),
                    array('eq' => '0000-00-00 00:00:00'),
                ));

                $collection->addFieldToFilter('end_date', array(
                    //M1 > M2 Translation Begin (Rule 25)
                    //array('gteq' => now()),
                    array('gteq' => date('Y-m-d H:i:s')),
                    //M1 > M2 Translation End
                    array('null' => 1),
                    array('eq' => '0000-00-00 00:00:00'),
                ));
                break;

            case self::LIST_STATUS_DISABLED:
                $collection->addFieldToFilter('active', 0);
                break;

            case self::LIST_STATUS_ENDED:
                $collection->addFieldToFilter('active', 1);
                //M1 > M2 Translation Begin (Rule 25)
                //$collection->addFieldToFilter('end_date', array('lteq' => now()));
                $collection->addFieldToFilter('end_date', array('lteq' => date('Y-m-d H:i:s')));
                //M1 > M2 Translation End
                break;

            case self::LIST_STATUS_PENDING:
                $collection->addFieldToFilter('active', 1);
                //M1 > M2 Translation Begin (Rule 25)
                //$collection->addFieldToFilter('start_date', array('gteq' => now()));
                $collection->addFieldToFilter('start_date', array('gteq' => date('Y-m-d H:i:s')));
                //M1 > M2 Translation End
                break;
        }

        return $this;
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected($json = false)
    {
           $customer = $this->getCustomer();

        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $lists = $customer->getLists();
            foreach ($lists as $clist) {
                $this->_selected[$clist->getId()] = array('id' => $clist->getId());
            }
        }
        if($json){
                $lists = $customer->getLists();
                $jsonLists = [];
                 foreach ($lists as $clist) {
                     $jsonLists[$clist->getId()] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonLists); 
        }
        return $this->_selected;
    } 
    public function setCustomer($customerId)
    {
        if ($customerId) {
            $this->_customerModel = $this->customerCustomerFactory->create()->load($customerId);
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
            'customer_id' =>  $this->getCustomer()->getId(),
            '_current' => true,
        );
        return $this->getUrl('epicor_lists/epicorlists_customer/listsgrid', $params);
    }

}
