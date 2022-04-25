<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 * @method bool getOnLeft()
 * @method void setOnLeft(bool $bool)
 */
class Managesave extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_ORDER_READ = 'Epicor_Supplier::supplier_orders_read';
    const FRONTEND_RESOURCE_INVOICE_READ = 'Epicor_Supplier::supplier_invoices_read';
    const FRONTEND_RESOURCE_RFQ_READ = 'Epicor_Supplier::supplier_rfqs_read';
    const FRONTEND_RESOURCE_PAYMENT_READ = 'Epicor_Supplier::supplier_payments_read';
    /**
     *  @var \Magento\Framework\DataObject
     */
    protected $_infoData = array();
    protected $_extraData = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    protected $urlEncoder;

    protected $scopeConfig;

    protected $dashboardInformation;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        $this->scopeConfig = $context->getScopeConfig();
        $this->getDashboardInformation();
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     *
     * @return \Epicor\Supplierconnect\Helper\Data
     */
    public function getHelper()
    {
        return $this->supplierconnectHelper;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        return $this->_infoData;
    }

    public function getExtraData()
    {
        return $this->_extraData;
    }

    public function getDashboardInformation() {
        if(!$this->dashboardInformation) {
            $this->dashboardInformation=   $this->getHelper()->getDashboardInformation();
        }
        return $this->dashboardInformation;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    public function getEnableRfqsSupplier() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_rfqs_supplier'];
        }
    }

    public function getEnableSummarySection() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_summary_section'];
        }
    }

    public function getRfqsFilter() {
        if(empty($this->dashboardInformation)) {
            $filterVals  ='Today,ThisWeek,Future,Open,Overdue';
        } else {
            $filterVals =  $this->dashboardInformation['rfqs_filter'];
        }
        $myArray = explode(',', $filterVals);
        $returnVals = array();
        foreach ($myArray as $key=>$value) {
            $returnVals[$value] = $value;
        }
        return $returnVals;
    }

    public function getEnableRfqsSupplierGrid() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_rfqs_supplier_grid'];
        }
    }

    public function getRfqsFrom() {
        if(empty($this->dashboardInformation)) {
            return "30d";
        } else {
            return $this->dashboardInformation['rfqs_from'];
        }
    }

    public function getRfqsSupplierCount() {
        if(empty($this->dashboardInformation)) {
            return 5;
        } else {
            return $this->dashboardInformation['rfqs_supplier_count'];
        }
    }


    public function getEnableOrderSupplier() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_order_supplier'];
        }
    }

    public function getOrderFilter() {
        if(empty($this->dashboardInformation)) {
            $filterVals  ='Open,POLineReleaseChanges';
        } else {
            $filterVals =  $this->dashboardInformation['order_filter'];
        }
        $myArray = explode(',', $filterVals);
        $returnVals = array();
        foreach ($myArray as $key=>$value) {
            $returnVals[$value] = $value;
        }
        return $returnVals;
    }

    public function getEnableOrderSupplierGrid() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_order_supplier_grid'];
        }
    }

    public function getOrderFrom() {
        if(empty($this->dashboardInformation)) {
            return "30d";
        } else {
            return $this->dashboardInformation['order_from'];
        }
    }

    public function getOrderSupplierCount() {
        if(empty($this->dashboardInformation)) {
            return 5;
        } else {
            return $this->dashboardInformation['order_supplier_count'];
        }
    }


    public function getEnableInvoiceSupplierGrid() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_invoice_supplier_grid'];
        }
    }

    public function getInvoiceFrom() {
        if(empty($this->dashboardInformation)) {
            return "30d";
        } else {
            return $this->dashboardInformation['invoice_from'];
        }
    }

    public function getInvoiceSupplierCount() {
        if(empty($this->dashboardInformation)) {
            return 5;
        } else {
            return $this->dashboardInformation['invoice_supplier_count'];
        }
    }


    public function getEnablePaymentSupplierGrid() {
        if(empty($this->dashboardInformation)) {
            return true;
        } else {
            return $this->dashboardInformation['enable_payment_supplier_grid'];
        }
    }

    public function getPaymentFrom() {
        if(empty($this->dashboardInformation)) {
            return "30d";
        } else {
            return $this->dashboardInformation['payment_from'];
        }
    }

    public function getPaymentSupplierCount() {
        if(empty($this->dashboardInformation)) {
            return 5;
        } else {
            return $this->dashboardInformation['payment_supplier_count'];
        }
    }



}
