<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
/**
 * This class is responsible for setting template in newly created list tab under customer edit section
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Lists extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'epicor/lists/customer/tab/lists.phtml';

    const LIST_STATUS_ACTIVE = 'A';
    const LIST_STATUS_DISABLED = 'D';
    const LIST_STATUS_ENDED = 'E';
    const LIST_STATUS_PENDING = 'P';

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
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */

    protected $_customerModel;
    

    public $_form;
    public $_formData;
    public $_account;
    public $_type;
    public $_default = array();
    public $_prefix = array();

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    )
    {
        
        
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        $this->registry = $registry;
        $this->listsHelper = $listsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commonHelper = $commonHelper;
        
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->getCustomer();
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_title = 'Contracts';
        $this->_type = 'customer';
        $this->_default = array('customer' => 'ERP Account Default', 'erpaccount' => 'Global Default');
        $this->_prefix = array('customer' => 'ecc_', 'erpaccount' => '');
        
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer()
    {
        if (!$this->_customerModel) {
            $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
            $this->_customerModel = $this->customerCustomerFactory->create()->load($customerId);
        }
        return $this->_customerModel;
    }
    
    
    /**
     * Initialize the form.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
   
    protected function _prepareForm()
    {

        $this->_form = $this->formFactory->create();
        $this->_formData = $this->backendSession->getFormData(true);
        $customer = $this->getCustomer();
        if (empty($this->_formData)) {
            $this->_formData = $this->getCustomer()->getData();
        }
        
        if ($customer->getId() == false) {
            $this->_form->setValues($this->_formData);
            $this->setForm($this->_form);

            return parent::_prepareForm();
        }
        
        if ($customer->getId()) {
            
            $fieldset = $this->_form->addFieldset('contracts_default', array('legend' => __('Default Contract Settings')));
            $eccContractsFilter = $fieldset->addField('ecc_contracts_filter', 'multiselect', array(
                'label' => __('Contract Filter'),
                'required' => false,                    
                'data-form-part' => $this->getData('target_form'),
                'name' => 'ecc_contracts_filter',
                'values' => $this->getContractHtml(),
            ));
            
            $eccContractsFilter->setAfterElementHtml("
                    <script> 
                     //<![CDATA[
                         var selectedOption = $('ecc_contracts_filter');
                         Event.observe('ecc_contracts_filter', 'change', function(event) {
                         for (i = 0; i < selectedOption.options.length; i++) {
                         var currentOption = selectedOption.options[i];
                         if (currentOption.selected && currentOption.value =='') {
                            for (var i=1; i<selectedOption.options.length; i++) {
                                selectedOption.options[i].selected = false;
                            }                         
                         }
                         }
                         })
                    //]]>
                    </script>
                    ");

            $ecc_default_contract = $fieldset->addField('ecc_default_contract', 'select', array(
                'label' => __('Default Contract'),
                'required' => false,
                'name' => 'ecc_default_contract',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => $this->getContractHtml(),
            ));
//            $ecc_default_contract->setAfterElementHtml("
//                    <script> 
//                     //<![CDATA[
//                     var reloadurl = '" . $this->getUrl('adminhtml/epicorlists_list/fetchaddress/') . "';
//                        Event.observe('ecc_default_contract', 'change', function(event) {
//                            fetchAddressInList(reloadurl);
//                        });
//                       fetchAddressInList(reloadurl);
//                    //]]>
//                    </script>
//                    ");


            $fieldset->addField('ecc_default_contract_address', 'select', array(
                'label' => __('Default Contract Address'),
                'required' => false,
                'id' => 'ecc_default_contract_address',
                'name' => 'ecc_default_contract_address',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => $this->customerSelectedAddressById($customer),
            ));
        }
        if ($this->_type == 'erpaccount') {
            $fieldset = $this->_form->addFieldset('contracts_form', array('legend' => __('Contracts')));
            $fieldset->addField('allowed_contract_type', 'select', array(
                'label' => __('Allowed Contract Type'),
                'required' => false,
                'name' => 'allowed_contract_type',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Header Only'),
                        'value' => 'H',
                    ),
                    array(
                        'label' => __('Both Header and Line'),
                        'value' => 'B',
                    ),
                    array(
                        'label' => __('None'),
                        'value' => 'N',
                    ),
                ),
            ));
            $fieldset->addField('required_contract_type', 'select', array(
                'label' => __('Required Contract Type'),
                'required' => false,                    
                'data-form-part' => $this->getData('target_form'),
                'name' => 'required_contract_type',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Header'),
                        'value' => 'H',
                    ),
                    array(
                        'label' => __('Either Header or Line'),
                        'value' => 'E',
                    ),
                    array(
                        'label' => __('Optional'),
                        'value' => 'O',
                    ),
                ),
            ));
            $fieldset->addField('allow_non_contract_items', 'select', array(
                'label' => __('Allow Non Contract Items'),
                'required' => false,
                'name' => 'allow_non_contract_items',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }

        if ($this->scopeConfig->getValue('epicor_lists/contracts/shipto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $fieldset = $this->_form->addFieldset($this->_prefix[$this->_type] . 'contracts_shipto_form', array('legend' => __('Ship To Settings')));
            if ($this->scopeConfig->getValue('epicor_lists/contracts/shiptoselection', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_shipto_default', 'select', array(
                    'label' => __('Contracts Ship To Default'),
                    'required' => false,                    
                    'data-form-part' => $this->getData('target_form'),
                    'name' => $this->_prefix[$this->_type] . 'contract_shipto_default',                    
                    'data-form-part' => $this->getData('target_form'),
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Shoppers Default Ship To'),
                            'value' => 'default',
                        ),
                        array(
                            'label' => __('Specific Ship To'),
                            'value' => 'specific',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }
            if ($this->scopeConfig->getValue('epicor_lists/contracts/shiptodate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_shipto_date', 'select', array(
                    'label' => __('Contract Ship To Date'),
                    'required' => false,
                    'name' => $this->_prefix[$this->_type] . 'contract_shipto_date',                    
                    'data-form-part' => $this->getData('target_form'),
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Newest Activation Date'),
                            'value' => 'newest',
                        ),
                        array(
                            'label' => __('Oldest Activation Date'),
                            'value' => 'oldest',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }

            $fieldset->addField($this->_prefix[$this->_type] . 'contract_shipto_prompt', 'select', array(
                'label' => __('Contract Ship To Prompt'),
                'required' => false,
                'name' => $this->_prefix[$this->_type] . 'contract_shipto_prompt',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }

        if ($this->scopeConfig->getValue('epicor_lists/contracts/header', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $fieldset = $this->_form->addFieldset('contracts_header', array('legend' => __('Header Contract Settings')));
            if ($this->scopeConfig->getValue('epicor_lists/contracts/headerselection', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_header_selection', 'select', array(
                    'label' => __('Contract Header Selection'),
                    'required' => false,
                    'name' => $this->_prefix[$this->_type] . 'contract_header_selection',                    
                    'data-form-part' => $this->getData('target_form'),
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Newest'),
                            'value' => 'newest',
                        ),
                        array(
                            'label' => __('Oldest'),
                            'value' => 'oldest',
                        ),
                        array(
                            'label' => __('Most Recently Used'),
                            'value' => 'recent',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }


            $fieldset->addField($this->_prefix[$this->_type] . 'contract_header_prompt', 'select', array(
                'label' => __('Contract Header Prompt'),
                'required' => false,
                'name' => $this->_prefix[$this->_type] . 'contract_header_prompt',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));

            $fieldset->addField($this->_prefix[$this->_type] . 'contract_header_always', 'select', array(
                'label' => __('Contract Header Always'),
                'required' => false,
                'name' => $this->_prefix[$this->_type] . 'contract_header_always',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }


        if ($this->scopeConfig->getValue('epicor_lists/contracts/line', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $fieldset = $this->_form->addFieldset('contracts_line', array('legend' => __('Line Contract Selection Settings')));
            if ($this->scopeConfig->getValue('epicor_lists/contracts/lineselection', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_line_selection', 'select', array(
                    'label' => __('Contract Line Selection'),
                    'required' => false,
                    'name' => $this->_prefix[$this->_type] . 'contract_line_selection',                    
                    'data-form-part' => $this->getData('target_form'),
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Lowest'),
                            'value' => 'lowest',
                        ),
                        array(
                            'label' => __('Highest'),
                            'value' => 'highest',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }


            $fieldset->addField($this->_prefix[$this->_type] . 'contract_line_prompt', 'select', array(
                'label' => __('Contract Line Prompt'),
                'required' => false,
                'name' => $this->_prefix[$this->_type] . 'contract_line_prompt',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));

            $fieldset->addField($this->_prefix[$this->_type] . 'contract_line_always', 'select', array(
                'label' => __('Contract Line Always'),
                'required' => false,
                'name' => $this->_prefix[$this->_type] . 'contract_line_always',                    
                'data-form-part' => $this->getData('target_form'),
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }

        $fieldset->addField('in_customer_lists_grid', 'hidden',
               ['name' => 'in_customer_lists_grid','data-form-part' => $this->getData('target_form'), 'id' => 'in_customer_lists_grid']);
       $fieldset->addField('in_customer_lists_grid_old', 'hidden', ['name' => 'in_customer_lists_grid_old']);
       
       
        $this->_form->setValues($this->_formData);
        $this->setForm($this->_form);

        return parent::_prepareForm();
    }

    /**
     * Get customer address
     *
     * @param $addressId
     * @param $customerId
     * @return string $options
     */
    function customerSelectedAddressById($customer)
    {
        $options = [];
        if ($customerId = $customer->getId()) {
            $loadHelper = $this->commonHelper->customerListAddressesById(0, $customerId);
            $customerData = $customer;
            $defaultContractAddress = $customerData->getEccDefaultContractAddress();
            $options[] = ['label' => 'No Default Address', 'value' => ''];
            if ($loadHelper) {
                foreach ($loadHelper as $code => $address) {
                    $options[] = ['label' =>  $address->getName() , 'value' => $code];
                }
            }
        }
        return $options;
    }
    /**
     * Get customer contract 
     * @return array
     */
    public function getContractHtml()
    {
        /* @var $customer Epicor_Comm_Model_Customer */
        $customer = $this->getCustomer();
        $contracts = $this->commonHelper->customerListsById($customer->getId(), 'filterContracts');
        
        $messages[] = array('label' => 'No Default Contract', 'value' => '');
        foreach ($contracts['items'] as $info) {
            $code = $info['id'];
            $messages[] = array(
                'label' => $info['title'],
                'value' => $code,
            );
        }
        return $messages;
    }
    
    
    
    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'cust_lits_grid',
            $this->getLayout()->createBlock(
                'Epicor\Lists\Block\Adminhtml\Customer\Edit\Tab\Lists\Grid',
                'customer.lists.grid'
            )
        );
        parent::_prepareLayout();
        return $this;
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

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $customer = $this->getCustomer();
            /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
            foreach ($customer->getLists() as $clist) {
                $this->_selected[$clist->getId()] = array('id' => $clist->getId());
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected)
    {
        // print_r($selected);die;
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
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
            'status', array(
            'header' => __('Current Status'),
            'index' => 'active',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'renderer' => 'epicor_lists/adminhtml_widget_grid_column_renderer_active',
            'type' => 'options',
            'options' => array(
                self::LIST_STATUS_ACTIVE => __('Active'),
                self::LIST_STATUS_DISABLED => __('Disabled'),
                self::LIST_STATUS_ENDED => __('Ended'),
                self::LIST_STATUS_PENDING => __('Pending')
            ),
            'filter_condition_callback' => array($this, '_statusFilter'),
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

    
    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
    public function getTabUrl()
    {
        return '';
    }

    public function getTabClass()
    {
        return '';
    }
    
    public function canShowTab()
    {
        $accountType = $this->_customerModel->getEccErpAccountType();
        $show = ($accountType =='supplier') ? FALSE : TRUE;
        return $show;
    }

    public function getTabLabel()
    {
        return 'Lists';
    }

    public function getTabTitle()
    {
        return 'Lists';
    }

    public function isHidden()
    {
        $customer = $this->getCustomer();
        //If the customer type is supplier, then hide the Lists tabs in Admin->customer->edit
        if ($customer->getEccErpAccountType() == "supplier") {
            return true;
        } else {
            return false;
        }
    }

    public function getGridUrl()
    {
        $customer = $this->getCustomer();
        $params = array(
            'id' => $customer->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('*/epicorlists_customer/listsgrid', $params);
    }

}
