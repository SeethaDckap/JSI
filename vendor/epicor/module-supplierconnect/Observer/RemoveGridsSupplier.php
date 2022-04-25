<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Supplierconnect\Observer;

class RemoveGridsSupplier extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $customerSession;


    protected $scopeConfig;

    protected $_request;

    protected $supplierconnectHelper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Supplierconnect\Model\ModelReader $modelReader,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_request  = $request;
        $this->supplierconnectHelper = $supplierconnectHelper;
        parent::__construct($registry, $commEntityregHelper, $eventManager, $modelReader,$customerSession);
    }


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $this->customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        if ($customer->isSupplier()) {

            $handle = $this->_request->getFullActionName();
            if($handle =="supplierconnect_account_index") {
                $layout = $observer->getLayout();
                $dashboardVals = $this->getDashboardInformation();
                if(empty($dashboardVals)) {
                    $enableSummary = true;
                    $enableRfqs= true;
                    $enableOrder = true;
                    $rfq_filter = 'Today,ThisWeek,Future,Open,Overdue';
                    $order_filter='Open,POLineReleaseChanges';
                    $enable_rfqs_supplier_grid = true;
                    $enable_order_supplier_grid = true;
                    $enable_invoice_supplier_grid = true;
                    $enable_payment_supplier_grid = true;
                } else {
                    $enableSummary= ($dashboardVals['enable_summary_section']  =="1") ? true: false;
                    $enableRfqs = ($dashboardVals['enable_rfqs_supplier']  =="1") ? true: false;
                    $rfq_filter =  $dashboardVals['rfqs_filter'];
                    $enableOrder = ($dashboardVals['enable_order_supplier'] =="1") ? true: false;
                    $order_filter =  $dashboardVals['order_filter'];
                    $enable_rfqs_supplier_grid =  ($dashboardVals['enable_rfqs_supplier_grid'] =="1") ? true: false;
                    $enable_order_supplier_grid = ($dashboardVals['enable_order_supplier_grid'] =="1") ? true: false;
                    $enable_invoice_supplier_grid = ($dashboardVals['enable_invoice_supplier_grid'] =="1") ? true: false;
                    $enable_payment_supplier_grid = ($dashboardVals['enable_payment_supplier_grid'] =="1") ? true: false;

                }
                $rfqsCount = $enableRfqs;
                if (!$rfqsCount || !$enableSummary) {
                    $layout->unsetElement('supplier.account.rfqs');
                }

                $orderCount = $enableOrder;
                if (!$orderCount || !$enableSummary) {
                    $layout->unsetElement('supplier.purchase.orders');
                }

                $rfqsGrid = $enable_rfqs_supplier_grid;
                if (!$rfqsGrid) {
                    $layout->unsetElement('supplier.dashboard.rfqs');
                }

                $orderGrid = $enable_order_supplier_grid;
                if (!$orderGrid) {
                    $layout->unsetElement('supplier.dashboard.orders');
                }
                $invoiceGrid = $enable_invoice_supplier_grid;
                if (!$invoiceGrid) {
                    $layout->unsetElement('supplier.dashboard.invoices');
                }
                $paymentGrid = $enable_payment_supplier_grid;
                if (!$paymentGrid) {
                    $layout->unsetElement('supplier.dashboard.payments');
                }
            }
        }
    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
    }

}