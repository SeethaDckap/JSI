<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard\Invoices;


/**
 * Supplier Invoices list Grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Supplier
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Supplier::supplier_invoices_details';
    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    protected $scopeConfig;

    protected $invoiceCount;

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
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
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

        $this->setId('supplierconnect_recent_invoices');
        $this->setDefaultSort('invoice_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('suis');
        $this->setIdColumn('invoice_number');
        $this->setUseAjax(true);
        $dasboardInformation = $this->getDashboardInformation();
        if(empty($dasboardInformation)) {
            $invoiceFrom = "30d";
            $this->invoiceCount = 5;
        } else {
            $invoiceFrom = $dasboardInformation['invoice_from'];
            $this->invoiceCount = (isset($dasboardInformation['invoice_supplier_count'])) ? $dasboardInformation['invoice_supplier_count']: 5;
        }
        $today = date('m/d/Y');
        $thirtyDays = date('m/d/Y', strtotime('today - 29 days'));
        $lastThreeMonths = date('m/d/Y', strtotime('-3 months'));
        $filters = array();
        //$filters['status'] = 0;

        if($invoiceFrom =="all") {
        } elseif ($invoiceFrom =="30d") {
            $filters['invoice_date']['from'] = $thirtyDays;
            $filters['invoice_date']['to'] = $today;
        } elseif ($invoiceFrom =="3m") {
            $filters['invoice_date']['from'] = $lastThreeMonths;
            $filters['invoice_date']['to'] = $today;
        }
        $filters['invoice_status'] ="O";
        $this->setDefaultFilter($filters);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $invoiceCount = $this->invoiceCount;
        $this->setMaxResults($invoiceCount);
        $this->initColumns();
    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
    }

    public function getRowUrl($row)
    {

        $url = null;
        $accessHelper = $this->commonAccessHelper;
        if ($this->isRowUrlAllowed() && $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Invoices', 'details', '', 'Access')) {
            $helper = $this->supplierconnectHelper;
            /* @var $helper Epicor_Supplierconnect_Helper_Data */
            $erp_account_number = $helper->getSupplierAccountNumber();
            $invoice = [
                $erp_account_number,
                $row->getId()
            ];
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($invoice)));
            $url = $this->getUrl('*/invoices/details', array('invoice' => $requested, 'back' => $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()))));
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

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/invoicesgrid', array(
            '_current' => true
        ));
    }


}