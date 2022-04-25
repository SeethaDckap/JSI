<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Contract\Shipto;

/**
 * List Addresses Serialized Grid Frontend
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    private $_selected = array();

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\AddressFactory
     */
    protected $commCustomerAddressFactory;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Lists\Block\Contract\Renderer\AddressFactory
     */
    protected $listsContractRendererAddressFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
            \Epicor\Comm\Model\Customer\AddressFactory $commCustomerAddressFactory,
            \Epicor\Lists\Helper\Session $listsSessionHelper,
            \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
            \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
            \Epicor\Comm\Helper\Data $commHelper,
            \Epicor\Common\Helper\Data $commonHelper,
            \Epicor\Lists\Block\Contract\Renderer\AddressFactory $listsContractRendererAddressFactory,
            \Magento\Framework\DataObjectFactory $dataObjectFactory,
            \Magento\Customer\Model\SessionFactory $customerSession,
            \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->listsContractRendererAddressFactory = $listsContractRendererAddressFactory;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->commCustomerAddressFactory = $commCustomerAddressFactory;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->commHelper = $commHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $context->getEventManager();
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );
        $this->setId('addressesGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');
        $this->setPagerVisibility(true);
        $this->setCacheDisabled(true);
        $this->setSkipGenerateContent(true);
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');
    }

    /**
     * Build data for List Addresses
     *
     */
    protected function _prepareCollection() {
        $customerAddress = $this->commCustomerAddressFactory->create();
        /* @var $customerAddress Epicor_Comm_Model_Customer_Address */

        $collection = $customerAddress->getCustomerAddressesCollection();
        /* @var $collection Mage_Customer_Model_Entity_Address_Collection */
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */

        $filter = $sessionHelper->getValue('ecc_shipto_select_filter');
        if ($filter) {
            $collection->addAttributeToFilter('ecc_erp_address_code', array('in' => $filter));
        }

        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList() {
        $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('contract'));
        return $this->list;
    }

    /**
     * Build columns for List Addresses
     *
     *
     */
    protected function _prepareColumns() {
        $this->addColumn(
                'ecc_erp_address_code', array(
            'header' => __('Address Code'),
            'index' => 'ecc_erp_address_code',
            'filter_index' => 'ecc_erp_address_code',
            'type' => 'text'
                )
        );

        $this->addColumn(
                'full_name', array(
            'header' => __('Name'),
            'index' => 'full_name',
            'filter_index' => 'full_name',
            'type' => 'text'
                )
        );

        $this->addColumn(
                'flatt_address', array(
            'header' => __('Address'),
            'index' => 'flat_address',
            'filter_index' => 'flat_address',
            'type' => 'text',
            'renderer' => '\Epicor\Lists\Block\Contract\Renderer\Address',
            'filter_condition_callback' => array($this, '_addressFilter'),
                )
        );

        $this->addColumn(
                'email', array(
            'header' => __('Email'),
            'index' => 'email',
            'filter_index' => 'email',
            'type' => 'text'
                )
        );

        $this->addColumn(
                'select', array(
            'header' => __('Select'),
            'width' => '280',
            'index' => 'ecc_erp_address_code',
            'renderer' => '\Epicor\Lists\Block\Contract\Shipto\Grid\Renderer\Select',
            'links' => 'true',
            'getter' => 'getEccErpAddressCode',
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            'actions' => array(
                array(
                    'caption' => __('Select'),
                    'url' => array('base' => '*/*/selectShipto'),
                    'field' => 'shipto'
                ),
            )
                )
        );




        return parent::_prepareColumns();
    }

    protected function _addressFilter($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $clone = clone $collection;

        $filterIds = array();
        foreach ($clone->getItems() as $item) {
            /* @var $item Epicor_Lists_Model_ListModel */
            if (stripos($this->commHelper->getFlattenedAddress($item), $value) !== false) {
                $filterIds[] = $item->getId();
            }
        }

        $collection->addFieldToFilter('entity_id', array('in' => $filterIds));
    }

    /**
     * Checks whether addresses should be restricted
     * 
     * @return boolean
     */
    public function restrictAddressTypes() {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */

        return $helper->restrictAddressTypes();
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/shiptogrid', array('_current' => true));
    }
    
    public function getRowUrl($item) {
        return false;
    }

}
