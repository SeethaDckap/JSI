<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Customer\Edit\Tab\MultiErpAccount;

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
     * @var \Epicor\Common\Model\ResourceModel\CustomerErpaccount\CollectionFactory
     */
    protected $commonResourceErpAccountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\CustomerErpaccount\CollectionFactory $commonResourceErpAccountCollectionFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->commonResourceErpAccountCollectionFactory = $commonResourceErpAccountCollectionFactory;
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
        parent::_construct();

        $this->setId('ErpAccountsGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection() {
        $collection = $this->commonResourceErpAccountCollectionFactory->create();
        $customerId = $this->getCustomerId();
        $collection->addFieldToFilter('customer_id', array('eq' => $customerId));
        $collection->getSelect()->joinLeft(
            array('eav_attribute' => $collection->getTable('eav_attribute')), 'eav_attribute.attribute_code = "ecc_per_contact_id"', array('attribute_id')
        );
        $collection->getSelect()->joinLeft(
            array('customer_entity_text' => $collection->getTable('customer_entity_text')), 'customer_entity_text.attribute_id = eav_attribute.attribute_id AND customer_entity_text.entity_id = '.$customerId, array('contact_id' => 'value')
        );
        $collection->getSelect()->join(
            array('ecc_erp_account' => $collection->getTable('ecc_erp_account')), 'main_table.erp_account_id = ecc_erp_account.entity_id', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_account_number' => 'account_number', 'customer_short_code' => 'short_code', 'linked_erp_account_type'=>'account_type')
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('customer_company', array(
            'header' => __('Company'),
            'width' => '150',
            'index' => 'customer_company',
            'filter_index' => 'company',
            'sortable' => false
        ));

        $this->addColumn('customer_account_number', array(
            'header' => __('ERP Account Number'),
            'width' => '150',
            'index' => 'customer_account_number',
            'filter_index' => 'account_number',
            'sortable' => false
        ));

        $this->addColumn('customer_short_code', array(
            'header' => __('Short Code'),
            'width' => '150',
            'index' => 'customer_short_code',
            'filter_index' => 'short_code',
            'sortable' => false
        ));

        $this->addColumn('linked_erp_account_type', array(
            'header' => __('ERP Account Type'),
            'width' => '150',
            'index' => 'linked_erp_account_type',
            'filter_index' => 'account_type',
            'sortable' => false
        ));

        $this->addColumn('contact_code', array(
            'header' => __('Contact Code'),
            'width' => '150',
            'index' => 'contact_code',
            'filter_index' => 'contact_code',
            'sortable' => false
        ));

        $this->addColumn('contact_id', array(
            'header' => __('Contact ID'),
            'width' => '150',
            'index' => 'contact_id',
            'filter_index' => 'contact_id',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    /**
     *
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomerId()
    {
        if (!$this->_customerid) {
            $this->_customerid = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        }
        return $this->_customerid;

    }

    public function toHtml()
    {
        $customer = $this->_coreRegistry->registry('current_customer');
        if($customer->getId()) {
            $erpCount = $customer->getErpAcctCounts();
            if (!empty($erpCount)) {
                return parent::toHtml();
            }
        }
        return '';
    }

}
