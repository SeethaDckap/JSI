<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Contract\Select;

/**
 * Customer select page grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

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

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Address\Renderer\StreetFactory
     */
    protected $commCustomerAddressRendererStreetFactory;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
            \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
            \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Framework\DataObjectFactory $dataObjectFactory,
            \Epicor\Comm\Block\Customer\Address\Renderer\StreetFactory $commCustomerAddressRendererStreetFactory,
            \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
            \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
            \Magento\Checkout\Model\Cart $checkoutCart,
            array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commCustomerAddressRendererStreetFactory = $commCustomerAddressRendererStreetFactory;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $context->getEventManager();
        $this->commonHelper = $commonHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->checkoutCart = $checkoutCart;

        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );

        $this->setId('selectgrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setSkipGenerateContent(true);
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');
    }

    /**
     * Build data for List Products
     *
     * @return \Epicor\Lists\Block\Adminhtml\List\Edit\Tab\Products
     */
    protected function _prepareCollection() {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        $contracts = $helper->getActiveContracts();
        $contractIds = empty($contracts) ? array(0) : array_keys($contracts);
        $collection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_List_Collection */
        $collection->getSelect()->joinLeft(array('contracts' => $collection->getTable('ecc_contract')), 'main_table.id = contracts.list_id', array('po_number' => 'contracts.purchase_order_number'), null);
        $collection->addFieldToFilter('main_table.id', array('in' => $contractIds));
        $collection->getSelect()->group('main_table.id');
        $collection->addFieldToFilter('main_table.type', array('eq' => 'Co'));
        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
    }

    /**
     * Configuration of grid
     * @return \Magento\Backend\Block\Widget\Grid
     * Build columns for List Addresses
     */
    protected function _prepareColumns() {
        $this->addColumn(
                'title', array(
            'header' => __('Title'),
            'index' => 'title',
            'filter_index' => 'title',
            'type' => 'text'
                )
        );
        $this->addColumn(
                'erp_code', array(
            'header' => __('Code'),
            'index' => 'erp_code',
            'filter_index' => 'erp_code',
            'type' => 'text',
            'renderer' => '\Epicor\Lists\Block\Contract\Select\Grid\Renderer\Erpcode'
                )
        );

        $this->addColumn(
                'po_number', array(
            'header' => __('PO Number'),
            'index' => 'po_number',
            'filter_index' => 'purchase_order_number',
            'type' => 'text'
                )
        );

        $this->addColumn(
                'start_date', array(
            'header' => __('Start Date'),
            'index' => 'start_date',
            'filter_index' => 'start_date',
            'type' => 'datetime'
                )
        );

        $this->addColumn(
                'end_date', array(
            'header' => __('End Date'),
            'index' => 'end_date',
            'filter_index' => 'end_date',
            'type' => 'datetime'
                )
        );

        $selectAction = array(
            'caption' => __('Select'),
            'url' => array('base' => '*/*/selectContract'),
            'field' => 'contract',
        );
        $quote = $this->checkoutCart->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */
        if ($quote->hasItems()) {
            $message = __('Changing Contract may remove items from the cart that are not valid for the selected Contract. Do you wish to continue?');
            $selectAction['confirm'] = $message;
        }

        $this->addColumn(
                'select', array(
            'header' => __('Select'),
            'width' => '280',
            'index' => 'id',
            'renderer' => '\Epicor\Lists\Block\Contract\Select\Grid\Renderer\Select',
            'links' => 'true',
            'getter' => 'getId',
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            'actions' => array(
                $selectAction,
                array(
                    'caption' => __('View Products'),
                    'url' => array('base' => '*/*/productsgrid'),
                    'field' => 'contract',
                    'id' => 'productGridUrl',
                    'onclick' => "productSelector.openpopup('ecc_contract_products',this.href); return false;",
                ),
                array(
                    'caption' => __('View Address'),
                    'id' => 'addressGridUrl',
                    'url' => array('base' => '*/*/addressesgrid'),
                    'field' => 'contract',
                    'onclick' => "addressSelector.openpopup('ecc_contract_address',this.href); return false;",
                ),
            )
                )
        );
        return parent::_prepareColumns();
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/selectgrid', array('_current' => true));
    }

    public function getRowUrl($item) {
        return false;
    }
        protected function _toHtml()
    {
        $html = parent::_toHtml(true);

        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        $allowed = $helper->allowedContractType();
        $required = $helper->requiredContractType();
        $isAjax = $this->getRequest()->getParam('ajax');
        if (($allowed == 'B' && in_array($required, array('E', 'O')) && !$isAjax) || ((in_array($required, array('O'))) && (!$isAjax))) {
            $url = $this->getUrl('*/*/selectContract', array('contract' => -1));
            $message = __('Continue without selecting a Contract');
            $html .= '<button title="' . $message . '" type="button" class="scalable" onclick="javascript:window.location=\'' . $url . '\'"><span><span>' . $message . '</span></span></button>';
        }

        return $html;
    }

}
