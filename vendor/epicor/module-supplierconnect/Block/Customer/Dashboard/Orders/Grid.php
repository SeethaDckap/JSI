<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard\Orders;


/**
 * Supplier Orders list Grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Supplier
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Supplier::supplier_orders_details';
    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\ReorderFactory
     */
    protected $customerconnectCustomerDashboardOrdersRendererReorderFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $scopeConfig;

    protected $orderCount;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionArrayFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setId('supplierconnect_recent_orders');
        $this->setDefaultSort('purchase_order_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('spos');
        $this->setIdColumn('purchase_order_number');
        $this->setUseAjax(true);
        $dasboardInformation = $this->getDashboardInformation();
        if(empty($dasboardInformation)) {
            $orderFrom = "30d";
            $this->orderCount = 5;
        } else {
            $orderFrom = $dasboardInformation['order_from'];
            $this->orderCount = (isset($dasboardInformation['order_supplier_count'])) ? $dasboardInformation['order_supplier_count']: 5;
        }
        $today = date('m/d/Y');
        $thirtyDays = date('m/d/Y', strtotime('today - 29 days'));
        $lastThreeMonths = date('m/d/Y', strtotime('-3 months'));
        $filters = array();
        //$filters['status'] = 0;

        if($orderFrom =="all") {
        } elseif ($orderFrom =="30d") {
            $filters['order_date']['from'] = $thirtyDays;
            $filters['order_date']['to'] = $today;
        } elseif ($orderFrom =="3m") {
            $filters['order_date']['from'] = $lastThreeMonths;
            $filters['order_date']['to'] = $today;
        }
        $filters['order_status'] ="O";
        $this->setDefaultFilter($filters);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $orderCount = $this->orderCount;
        $this->setMaxResults($orderCount);
        $this->initColumns();
    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
    }

    public function getRowUrl($row)
    {

        $url = null;
        $accessHelper = $this->commonAccessHelper;
        if ($this->isRowUrlAllowed() && $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Orders', 'details', '', 'Access')) {
            $helper = $this->supplierconnectHelper;
            /* @var $helper Epicor_Supplierconnect_Helper_Data */
            $erp_account_number = $helper->getSupplierAccountNumber();
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getId()));
            $url = $this->getUrl('*/orders/details', array('order' => $requested, 'back' => $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()))));
        }

        return $url;
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        if ($this->listsFrontendContractHelper->contractsDisabled()) {
            unset($columns['contracts_contract_code']);
        }

        $this->setCustomColumns($columns);
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/ordergrid', array(
            '_current' => true
        ));
    }

}