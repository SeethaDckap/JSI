<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/**
 * Erp account Lists grid
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Lists extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_erp_customer;
    private $_selected = array();
    private $_erpSystem = array('p21');

    const LIST_STATUS_ACTIVE = 'A';
    const LIST_STATUS_DISABLED = 'D';
    const LIST_STATUS_ENDED = 'E';
    const LIST_STATUS_PENDING = 'P';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory
     */
    protected $listsResourceListModelErpAccountCollectionFactory;

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
        \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory $listsResourceListModelErpAccountCollectionFactory,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->listsResourceListModelErpAccountCollectionFactory = $listsResourceListModelErpAccountCollectionFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpaccount_lists');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('lists' => 1));
        $this->setSaveParametersInSession(true);
        $checkEditable = $this->checkErp();
        if (!$checkEditable) {
            //Global Contract Settings should not be amendable on ERP Accounts on Customer if ECC is linked to ERP "P21"
            $this->setAdditionalJavaScript("document.getElementById('allowed_contract_type').disabled=true;
                                            document.getElementById('required_contract_type').disabled=true;
                                            document.getElementById('allow_non_contract_items').disabled=true;");
        }
    }

    public function checkErp()
    {
        $erp = $this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (in_array($erp, $this->_erpSystem)) {
            $editable = false;
        } else {
            $editable = true;
        }
        return $editable;
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
        return 'Lists';
    }

    public function getTabTitle()
    {
        return 'Lists';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $erpAccountType = $this->getErpCustomer()->getAccountType();
        switch ($erpAccountType) {
            case "B2B":
                $ercAccountLinkType = "B";
                break;
            case "B2C":
                $ercAccountLinkType = "C";
                break;
            case "Dealer":
                $ercAccountLinkType = "D";
                break;  
            case "Distributor":
                $ercAccountLinkType = "D";
                break;              
        }

        $collection = $this->listsResourceListModelCollectionFactory->create()->addFieldToFilter('erp_account_link_type', array($ercAccountLinkType, 'E'))
            ->addFieldToFilter('type', array('neq' => 'Co'));


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $collection = $this->listsResourceListModelErpAccountCollectionFactory->create()->addFieldToFilter('erp_account_id', $this->getErpCustomer()->getId());

            foreach ($collection->getData() as $listData) {
                $this->_selected[$listData['list_id']] = array('id' => $listData['list_id']);
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

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'lists') {

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
        $this->addColumn('lists', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'lists',
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
            'header' => __('Erp code'),
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
            'renderer' => 'Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Active',
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

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/listsgrid', $params);
    }

}
