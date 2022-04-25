<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard\Payments;


/**
 * Supplier Payments list Grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Supplier Connect
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{

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

    protected $paymentCount;


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

        $this->setId('supplierconnect_recent_payments');
        $this->setDefaultSort('payment_date');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('sups');
        $this->setIdColumn('invoice_number');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $dasboardInformation = $this->getDashboardInformation();
        if(empty($dasboardInformation)) {
            $paymentFrom = "30d";
            $this->paymentCount = 5;
        } else {
            $paymentFrom = $dasboardInformation['payment_from'];
            $this->paymentCount = (isset($dasboardInformation['payment_supplier_count'])) ? $dasboardInformation['payment_supplier_count']: 5;
        }
        $today = date('m/d/Y');
        $thirtyDays = date('m/d/Y', strtotime('today - 29 days'));
        $lastThreeMonths = date('m/d/Y', strtotime('-3 months'));
        $filters = array();
        if($paymentFrom =="all") {
        } elseif ($paymentFrom =="30d") {
            $filters['payment_date']['from'] = $thirtyDays;
            $filters['payment_date']['to'] = $today;
        } elseif ($paymentFrom =="3m") {
            $filters['payment_date']['from'] = $lastThreeMonths;
            $filters['payment_date']['to'] = $today;
        }
        $filters['status'] ="O";
        $this->setDefaultFilter($filters);
        $this->setCacheDisabled(true);
        $paymentCount = $this->paymentCount;
        $this->setMaxResults($paymentCount);

        $this->initColumns();
    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
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
        return $this->getUrl('*/dashboard/paymentsgrid', array(
            '_current' => true
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

}