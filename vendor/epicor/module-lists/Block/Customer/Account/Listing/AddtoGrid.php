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
class AddtoGrid extends \Epicor\Common\Block\Widget\Grid\Extended
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
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;



    protected $_template = 'Epicor_Common::widget/grid/extended.phtml';
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
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        array $data = []
    )
    {
        $this->commHelper = $commHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->customerSession = $customerSession;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->storeManager = $context->getStoreManager();

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
    }

    protected function _prepareCollection()
    {
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $customerAccountType = $this->customerSession->getCustomer()->getEccErpAccountType();
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
        $collection->getSelect()->where(new \Zend_Db_Expr("(source IN('customer')) OR (source = 'web' AND settings NOT LIKE '%M%' )"));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _masterShopperList($collection, $erpAccountId)
    {
        $customerSessionvar = $this->customerSession->getCustomer();
        $isMasterShopper = $customerSessionvar->getData('ecc_master_shopper');
        $customerId = $customerSessionvar->getData('entity_id');
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
            $listsUrl =  $this->customerSession->getAddtoListsUrl();
            $poductInfo = str_replace("lists/lists/saveCartToExistingList/","",$listsUrl);
            $urlRedirect = $urlRedirect.$poductInfo;
            $this->setChild(
                'add_button',
                $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setData(
                        array(
                            'label' => __('Add New List'),
                            'onclick' => "location.href='$urlRedirect';",
                            'class' => 'task'
                        )
                    )
            );
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
        return parent::getMainButtonsHtml().$this->getAddButtonHtml();
    }


    protected function _prepareColumns()
    {
        $typeModel = $this->listsListModelTypeFactory->create();
        $isMasterShopper = $this->customerSession->getCustomer()->getData('ecc_master_shopper');
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

        $actions[] = array(
            'caption' => __('Add to List'),
            'url' => array(
                'base' => $this->customerSession->getAddtoListsUrl(),
            ),
            'id' => 'addtolist',
            'field' => 'id'
        );


        $this->addColumn('action', [
            'header' => __('Action'),
            'width' => '100',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => true,
            'getter' => 'getId',
            'actions' => $actions,
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ]);
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/listgridaddto', array(
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
            default:
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
                var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
            });
       ";
    }

}
