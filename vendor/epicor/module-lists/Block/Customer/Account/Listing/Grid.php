<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Listing;


/**
 * Customer  list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Widget\Grid\Extended
{

    const LIST_STATUS_ACTIVE = 'A';
    const LIST_STATUS_DISABLED = 'D';
    const LIST_STATUS_ENDED = 'E';
    const LIST_STATUS_PENDING = 'P';

    protected $_erp_customer;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Lists\Block\Customer\Account\Listing\Renderer\CreatedbyFactory
     */
    protected $listsCustomerAccountListingRendererCreatedbyFactory;

    /*
     * @var \Epicor\Lists\Block\Customer\Account\Listing\Renderer\EditlistFactory
     */
    protected $listsCustomerAccountListingRendererEditlistFactory;


    /**
     * @var \Epicor\Common\Model\Message\CollectionFactory
     */
    protected $commonMessageCollectionFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    protected $_template = 'Epicor_Common::widget/grid/extended.phtml';
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_accessauthorization;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Lists\Block\Customer\Account\Listing\Renderer\CreatedbyFactory $listsCustomerAccountListingRendererCreatedbyFactory,
        \Epicor\Lists\Block\Customer\Account\Listing\Renderer\EditlistFactory $listsCustomerAccountListingRendererEditlistFactory,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->listsCustomerAccountListingRendererCreatedbyFactory = $listsCustomerAccountListingRendererCreatedbyFactory;
        $this->listsCustomerAccountListingRendererEditlistFactory = $listsCustomerAccountListingRendererEditlistFactory;
        $this->commHelper = $commHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->customerSession = $customerSession;
        $this->listsHelper = $listsHelper;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->storeManager = $context->getStoreManager();

        $this->commonMessageCollectionFactory = $commonMessageCollectionFactory;
        $this->commonHelper = $commonHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->request = $request;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('listgrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setNoFilterMassactionColumn(true);
        $this->_accessauthorization = $context->getAccessAuthorization();
        // $this->setCustomData($collection->getItems());
    }

    protected function _prepareCollection()
    {
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $customerSession = $this->customerSession->getCustomer();
        $isMasterShopper = $customerSession->getData('ecc_master_shopper');
        $customerId = $customerSession->getData('entity_id');
        $customerAccountType = $customerSession->getEccErpAccountType();
        $collection = $this->listsResourceListModelCollectionFactory->create();
        // dont display lists which are excluded  
        $collection->addFieldToFilter('erp_accounts_exclusion', array('eq' => 'N'));
        $collection->getSelect()->joinLeft(array(
            'lea' => $collection->getTable('ecc_list_erp_account')
        ), 'lea.list_id=main_table.id', array(
            'lea.erp_account_id'
        ));
        $erpAccountId = $erpAccount->getId();
        $this->_masterShopperList($collection, $erpAccountId);
        if ($customerAccountType != "guest") {
            $collection->addFieldToFilter('lea.erp_account_id', $erpAccount->getId());
        }
        //only sees lists with a list type of pre-defined or favourite or product group
        $collection->addFieldToFilter('type', array('in' => array('Pl', 'Fa', 'Pg')));
        $needle = "M";
        $collection->getSelect()->where(new \Zend_Db_Expr("(source IN('customer')) OR (source = 'web' AND settings NOT LIKE '%M%' )"));
        //below no longer required if non master shoppers can view all lists. Commented out in case required later
//        if ($isMasterShopper) {
//            //Lists assigned to their ERP Account with a source of “customer”
//            //Lists assigned to their ERP Account with a source of “web” that meet certain criteria (Non Mandatory (i.e. does not have the “M” setting))
//            $collection->getSelect()->where(new \Zend_Db_Expr("(source IN('customer')) OR (source = 'web' AND settings NOT LIKE '%M%' )"));
//
//            //moved this to within this block as when not mastershopper, no products were appearing
//        } else {
//            $collection->addFieldToFilter('source', array('eq' => array('customer')));
//      //      $collection->addFieldToFilter('owner_id', array('eq' => $customerId));
//        }
        //$collection->getSelect()->group('lea.list_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _masterShopperList($collection, $erpAccountId)
    {
        $customerSession = $this->customerSession->getCustomer();
        $isMasterShopper = $customerSession->getData('ecc_master_shopper');
        $customerId = $customerSession->getData('entity_id');
        //A master shopper only sees (and can only amend and delete) lists with a list type of pre-defined or favourite 
        //and which are assigned to his ERP Account and no other ERP Account. 
        $eTableName = $collection->getTable('ecc_list_erp_account');

        if ($isMasterShopper) {
            $subquery = new \Zend_Db_Expr('SELECT lea.list_id FROM ' . $eTableName . ' AS lea WHERE lea.list_id = main_table.id AND lea.erp_account_id <> "' . $erpAccountId . '"');
            $collection->addFieldToFilter('lea.list_id', array('nin' => array($subquery)));
        } else {
            // A non master shopper/Registered shopper/Registered Guest 
            // only sees (and can only amend and delete) lists with a list type of pre-defined or favourite 
            // and which are assigned to his ERP Account and customer and no other ERP Account / customer 

            $cTableName = $collection->getTable('ecc_list_customer');
            $tableJoin = array('customer' => $cTableName);
            $tableCols = array('customer.customer_id' => 'customer_id');
            $collection->getSelect()->joinLeft($tableJoin, 'main_table.id = customer.list_id', $tableCols);

            //retrieve customers other than the current customer for a list
            // but remove any lists that have the current customer on it

            $customerSub = new \Zend_Db_Expr('SELECT lc.list_id FROM ' . $cTableName . ' AS lc WHERE lc.list_id = main_table.id AND lc.customer_id <> "' . $customerId . '" and lc.list_id <> ('
                . 'SELECT ld.list_id FROM ' . $cTableName . '  AS ld WHERE ld.list_id = main_table.id AND ld.customer_id = "' . $customerId . '")');


            // remove from collection any lists returned from above condition 
            $collection->addFieldToFilter('lea.list_id', array('nin' => array($customerSub)));

            //$subquery = new Zend_Db_Expr('SELECT customer.list_id FROM epicor_lists_list_customer AS customer WHERE customer.list_id = main_table.id AND customer.customer_id <> "'.$customerId.'"');
            $subqueryErp = new \Zend_Db_Expr('SELECT lea.list_id FROM ' . $eTableName . ' AS lea WHERE lea.list_id = main_table.id AND lea.erp_account_id <> "' . $erpAccountId . '"');
            $collection->addFieldToFilter('lea.list_id', array('nin' => array($subqueryErp)));
        }

        return $collection;
    }

    protected function _prepareLayout()
    {

        if ($this->_accessauthorization->isAllowed('Epicor_Customer::my_account_lists_create')) {
            $urlRedirect = $this->getUrl('*/*/new', array(
                '_current' => true,
                'contract' => $this->getRequest()->getParam('contract')
            ));
            $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(array(
                'label' => __('Add New List'),
                'onclick' => "location.href='$urlRedirect';",
                'class' => 'task'
            )));
        }
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Mage_Adminhtml_Block_Widget_Grid
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getAddButtonHtml();
        return $html;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml(false);
        return $html;
    }

    public function getRowUrl($row)
    {
//        return $this->getUrl('*/*/edit', array(
//            'id' => base64_encode($row->getId())
//        ));
    }

    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        $typeModel = $this->listsListModelTypeFactory->create();
        $customerSession = $this->customerSession->getCustomer();
        $isMasterShopper = $customerSession->getData('ecc_master_shopper');

//        $this->addColumn('id', array(
//            'header' => $this->__('ID'),
//            'index' => 'id',
//            //'width'    => '450px',
//            'column_css_class' => 'no-display',
//            'header_css_class' => 'no-display'
//        ));

        $this->addColumn('erp_code', array(
            'header' => __('Reference Code'),
            'index' => 'erp_code',
            'type' => 'text'
        ));

        $this->addColumn('type', array(
            'header' => __('Type'),
            //'width' => '350px',
            'index' => 'type',
            'type' => 'options',
            'options' => $typeModel->toListFilterArray()
        ));

        $this->addColumn('title', array(
            'header' => __('Title'),
            'index' => 'title',
            'type' => 'text'
        ));

        $this->addColumn('start_date', array(
            'header' => __('Start Date'),
            'index' => 'start_date',
            //'width'    => '350px',
            'type' => 'datetime'
        ));

        $this->addColumn('end_date', array(
            'header' => __('End Date'),
            'index' => 'end_date',
            //'width'    => '350px',
            'type' => 'datetime'
        ));

        $this->addColumn('status', array(
            'header' => __('Current Status'),
            'index' => 'active',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'renderer' => 'Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Active',
            'type' => 'options',
            'options' => array(
                self::LIST_STATUS_ACTIVE => __('Active'),
                self::LIST_STATUS_DISABLED => __('Disabled'),
                self::LIST_STATUS_ENDED => __('Ended'),
                self::LIST_STATUS_PENDING => __('Pending')
            ),
            'filter_condition_callback' => array(
                $this,
                '_statusFilter'
            )
        ));

        $this->addColumn('active', array(
            'header' => __('Active'),
            'index' => 'active',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
        ));


        if ($isMasterShopper) {
            $this->addColumn('owner_id', array(
                'header' => __('Created By'),
                'index' => 'owner_id',
                'type' => 'text',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Epicor\Lists\Block\Customer\Account\Listing\Renderer\Createdby',
            ));
        }
        $actions = [];

        if ($this->_accessauthorization->isAllowed('Epicor_Customer::my_account_lists_edit')) {
            $actions[] = array(
                'caption' => __('Edit'),
                'url' => array(
                    'base' => '*/*/edit'
                ),
                'id' => 'edit',
                'field' => 'id'
            );
            $actions[] = array(
                'caption' => __(' | '),
                'id' => 'separator',
            );
        }

        if ($this->_accessauthorization->isAllowed('Epicor_Customer::my_account_lists_delete')) {
            $actions[] = array(
                'caption' => __('Delete'),
                'url' => array(
                    'base' => '*/*/delete'
                ),
                'field' => 'id',
                'id' => 'delete',
                'confirm' => __('Are you sure you want to delete this List? This cannot be undone')
            );
            $actions[] = array(
                'caption' => __(' | '),
                'id' => 'separator',
            );
        }
        $eccHidePrices = $this->commHelper->getEccHidePrice();
        if($this->commonHelper->getScopeConfig()->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && $this->commonHelper->getScopeConfig()->isSetFlag('epicor_lists/savecartaslist/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($this->request->getFullActionName() == 'epicor_lists_lists_index'
                || $this->request->getFullActionName() == 'epicor_lists_lists_listgrid')
            && (!$eccHidePrices || $eccHidePrices == 3)
        ) {
            $actions[] = array(
                'caption' => __('Add List To Cart'),
                'url' => array(
                    'base' => '*/*/addListToCart'
                ),
                'field' => 'id',
                'id' => 'addListToCart',
            );
        }
        $this->addColumn('action', array(
                'header' => __('Action'),
                'width' => '150px',
                'renderer' => 'Epicor\Lists\Block\Customer\Account\Listing\Renderer\Editlist',
                'links' => 'true',
                'getter' => 'getId',
                'sortable' => false,
                'filter' => false,
                'column_css_class' => 'a-center ',
                'actions' => $actions,
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {

        if (!$this->_accessauthorization->isAllowed('Epicor_Customer::my_account_lists_edit')) {
            return $this;
        }
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('listid');

        $storeId = $this->storeManager->getStore()->getStoreId();

        if ($this->_accessauthorization->isAllowed('Epicor_Customer::my_account_lists_delete')) {
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Delete selected Lists?')
            ));
        }

        $customers = $this->commHelper->getErpAccountInfo()->getCustomers();
        // ->addFieldToFilter('entity_id', array('nin' => Mage::getSingleton('customer/session')->getId()));
        $customers->addAttributeToFilter('store_id', $storeId);

        $customerData = array();
        foreach ($customers as $filtered_customer) {
            $customerData[] = array(
                'value' => $filtered_customer->getId(),
                'label' => $filtered_customer->getName()
            );
        }

        $customerSession = $this->customerSession->getCustomer();
        $isMasterShoper = $customerSession->getData('ecc_master_shopper');

        if ((count($customerData) >= 1) && ($isMasterShoper)) {
            $this->getMassactionBlock()->addItem('assigncustomer', array(
                    'label' => __('Assign Customer'),
                    'url' => $this->getUrl('*/*/massAssignCustomer'),
                    'additional' => array(
                        'assign_customer' => array(
                            'name' => 'assign_customer',
                            'type' => 'select',
                            'values' => $customerData,
                            'label' => __('Customer')
                        )
                    )
                )
            );


            $this->getMassactionBlock()->addItem('removecustomer', array(
                    'label' => __('Remove Customer'),
                    'url' => $this->getUrl('*/*/massRemoveCustomer'),
                    'additional' => array(
                        'remove_customer' => array(
                            'name' => 'remove_customer',
                            'type' => 'select',
                            'values' => $customerData,
                            'label' => __('Customer')
                        )
                    )
                )
            );
        }

        //if lists and savecartaslist enabled
        if($this->commonHelper->getScopeConfig()->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && $this->commonHelper->getScopeConfig()->isSetFlag('epicor_lists/savecartaslist/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->getMassactionBlock()->addItem('massaddlisttocart', array(
                    'label' => __('Add List To Cart'),
                    'url' => $this->getUrl('*/*/massAddListToCart')
                )
            );
        }

        return $this;
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/listgrid', array(
            '_current' => true
        ));
    }

    public function _statusFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        switch ($value) {
            case self::LIST_STATUS_ACTIVE:
                $collection->filterActive();
                break;

            case self::LIST_STATUS_DISABLED:
                $collection->addFieldToFilter('active', 0);
                break;

            case self::LIST_STATUS_ENDED:
                $collection->addFieldToFilter('active', 1);
                $collection->addFieldToFilter('end_date', array(
                    //M1 > M2 Translation Begin (Rule 25)
                    //'lteq' => now()
                    'lteq' => date('Y-m-d H:i:s')
                    //M1 > M2 Translation End
                ));
                break;

            case self::LIST_STATUS_PENDING:
                $collection->addFieldToFilter('active', 1);
                $collection->addFieldToFilter('start_date', array(
                    //M1 > M2 Translation Begin (Rule 25)
                    //'gteq' => now()
                    'gteq' => date('Y-m-d H:i:s')
                    //M1 > M2 Translation End
                ));
                break;
        }

        return $this;
    }

    /*
     * This is instead of a sections.xml file and clears the cart section
     */
    public function getAdditionalJavascript()
    {
        return
            "require([
                'Magento_Customer/js/customer-data'
            ], function (customerData) {
                var sections = ['cart','customer-lists'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
            });
            require([
                'jquery',
                'Magento_Customer/js/customer-data'
             ], function($,customerData) {
                    $('#delete').on('click',function(){ 
                        var sections = ['customer-lists'];
                        customerData.invalidate(sections);
                        customerData.reload(sections, true);
                    });        
                }
            );";
    }

}
