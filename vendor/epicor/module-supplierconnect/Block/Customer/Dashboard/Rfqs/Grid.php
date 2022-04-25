<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard\Rfqs;


/**
 * Supplier Rfqs list Grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Supplier
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Supplier::supplier_rfqs_details';
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

    protected $dataObjectFactory;

    protected $rfqsCount;

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

        $this->setId('supplierconnect_recent_rfqs');
        $this->setDefaultSort('rfq_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('surs');
        $this->setIdColumn('rfq_number');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $dasboardInformation = $this->getDashboardInformation();
        if(empty($dasboardInformation)) {
            $RfqsFrom ="30d";
            $this->rfqsCount = 5;
        } else {
            $RfqsFrom = $dasboardInformation['rfqs_from'];
            $this->rfqsCount = (isset($dasboardInformation['rfqs_supplier_count'])) ? $dasboardInformation['rfqs_supplier_count']: 5;

        }
        $today = date('m/d/Y');
        $thirtyDays = date('m/d/Y', strtotime('today - 29 days'));
        $lastThreeMonths = date('m/d/Y', strtotime('-3 months'));
        $filters = array();
        //$filters['status'] = 0;

        if($RfqsFrom =="all") {
            //unset($filters['due_date']);
        } elseif ($RfqsFrom =="30d") {
            $filters['due_date']['from'] = $thirtyDays;
            $filters['due_date']['to'] = $today;
        } elseif ($RfqsFrom =="3m") {
            $filters['due_date']['from'] = $lastThreeMonths;
            $filters['due_date']['to'] = $today;
        }
        $filters['status'] ="O";
        $this->setDefaultFilter($filters);
        $this->setCacheDisabled(true);
        $rfqsCount =$this->rfqsCount;
        $this->setMaxResults($rfqsCount);
        $this->initColumns();
    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
    }

    public function getRowUrl($row)
    {

        $url = null;
        $accessHelper = $this->commonAccessHelper;
        if ($this->isRowUrlAllowed() && $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Rfq', 'details', '', 'Access')) {
            $helper = $this->supplierconnectHelper;
            /* @var $helper Epicor\Supplierconnect\Helper\Data */
            $erp_account_number = $helper->getSupplierAccountNumber();
            $rfq = [
                $erp_account_number,
                $row->getId(),
                $row->getLine()
            ];
            $rfq_requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($rfq)));
            $url = $this->getUrl('*/rfq/details', array('rfq' => $rfq_requested, 'back' => $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()))));
        }

        return $url;
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/rfqsgrid', array(
            '_current' => true
        ));
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


}