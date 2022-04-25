<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Dashboard;

class Managesave extends \Epicor\Supplierconnect\Controller\Dashboard
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuad
     */
    protected $customerconnectMessageRequestCuad;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    protected $configWriter;

    protected $scopeConfig;


    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    protected $supplierDashboardFactory;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Epicor\Supplierconnect\Model\ManageDashboardFactory $supplierDashboardFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->customerconnectMessageRequestCuad = $customerconnectMessageRequestCuad;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->supplierDashboardFactory = $supplierDashboardFactory;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $post = $this->getRequest()->getPost();

        $customer = $this->customerSession->getCustomer();
        $DashboardFactory = $this->supplierDashboardFactory->create();
        $DashboardFactory->load($customer->getId(),'customer_id');

        if(isset($post['enable_summary_supplier_hidden'])){
            if(isset($post['enable_summary_supplier'])) {
                $DashboardFactory->setEnableSummarySection(1);
            } else {
                $DashboardFactory->setEnableSummarySection(0);
            }
        }else{
            $DashboardFactory->setEnableSummarySection($DashboardFactory->getEnableSummarySection());
        }

        if(isset($post['enable_rfqs_supplier_hidden'])){
            if(isset($post['enable_rfqs_supplier'])) {
                $DashboardFactory->setEnableRfqsSupplier(1);
            } else {
                $DashboardFactory->setEnableRfqsSupplier(0);
            }
        }else{
            $DashboardFactory->setEnableRfqsSupplier($DashboardFactory->getEnableRfqsSupplier());
        }

        if(isset($post['rfqs_filter'])) {
            $implode = implode(',', $post['rfqs_filter']);
            $DashboardFactory->setRfqsFilter($implode);
        } else {
            $DashboardFactory->setRfqsFilter($DashboardFactory->getRfqsFilter());
        }

        if(isset($post['enable_rfqs_supplier_grid_hidden'])){
            if(isset($post['enable_rfqs_supplier_grid'])) {
                $DashboardFactory->setEnableRfqsSupplierGrid(1);
            } else {
                $DashboardFactory->setEnableRfqsSupplierGrid(0);
            }
        }else{
            $DashboardFactory->setEnableRfqsSupplierGrid($DashboardFactory->getEnableRfqsSupplierGrid());
        }
        if(isset($post['rfqs_from'])) {
            $DashboardFactory->setRfqsFrom($post['rfqs_from'][0]);
        }else{
            $DashboardFactory->setRfqsFrom($DashboardFactory->getRfqsFrom());
        }
        if(isset($post['rfqs_supplier_count'])) {
            $DashboardFactory->setRfqsSupplierCount($post['rfqs_supplier_count']);
        } else {
            $rfq_supplier_count = ($DashboardFactory->getRfqsSupplierCount() > 0 ) ? $DashboardFactory->getRfqsSupplierCount() : 5;
            $DashboardFactory->setRfqsSupplierCount($rfq_supplier_count);
        }

        if(isset($post['enable_order_supplier_hidden'])){
            if(isset($post['enable_order_supplier'])) {
                $DashboardFactory->setEnableOrderSupplier(1);
            } else {
                $DashboardFactory->setEnableOrderSupplier(0);
            }
        }else{
            $DashboardFactory->setEnableOrderSupplier($DashboardFactory->getEnableOrderSupplier());
        }

        if(isset($post['order_filter'])) {
            $implode = implode(',', $post['order_filter']);
            $DashboardFactory->setOrderFilter($implode);
        } else {
            $DashboardFactory->setOrderFilter($DashboardFactory->getOrderFilter());
        }

        if(isset($post['enable_order_supplier_grid_hidden'])){
            if(isset($post['enable_order_supplier_grid'])) {
                $DashboardFactory->setEnableOrderSupplierGrid(1);
            } else {
                $DashboardFactory->setEnableOrderSupplierGrid(0);
            }
        }else{
            $DashboardFactory->setEnableOrderSupplierGrid($DashboardFactory->getEnableOrderSupplierGrid());
        }
        if(isset($post['order_from'])) {
            $DashboardFactory->setOrderFrom($post['order_from'][0]);
        }else{
            $DashboardFactory->setOrderFrom($DashboardFactory->getOrderFrom());
        }
        if(isset($post['order_supplier_count'])) {
            $DashboardFactory->setOrderSupplierCount($post['order_supplier_count']);
        } else {
            $order_supplier_count = ($DashboardFactory->getOrderSupplierCount() > 0 ) ? $DashboardFactory->getOrderSupplierCount() : 5;
            $DashboardFactory->setOrderSupplierCount($order_supplier_count);
        }

        if(isset($post['enable_invoice_supplier_grid_hidden'])){
            if(isset($post['enable_invoice_supplier_grid'])) {
                $DashboardFactory->setEnableInvoiceSupplierGrid(1);
            } else {
                $DashboardFactory->setEnableInvoiceSupplierGrid(0);
            }
        }else{
            $DashboardFactory->setEnableInvoiceSupplierGrid($DashboardFactory->getEnableInvoiceSupplierGrid());
        }
        if(isset($post['invoice_from'])) {
            $DashboardFactory->setInvoiceFrom($post['invoice_from'][0]);
        }else{
            $DashboardFactory->setInvoiceFrom($DashboardFactory->getInvoiceFrom());
        }
        if(isset($post['invoice_supplier_count'])) {
            $DashboardFactory->setInvoiceSupplierCount($post['invoice_supplier_count']);
        } else {
            $invoice_supplier_count = ($DashboardFactory->getInvoiceSupplierCount()>0) ? $DashboardFactory->getInvoiceSupplierCount() : 5;
            $DashboardFactory->setInvoiceSupplierCount($invoice_supplier_count);
        }

        if(isset($post['enable_payment_supplier_grid_hidden'])){
            if(isset($post['enable_payment_supplier_grid'])) {
                $DashboardFactory->setEnablePaymentSupplierGrid(1);
            } else {
                $DashboardFactory->setEnablePaymentSupplierGrid(0);
            }
        }else{
            $DashboardFactory->setEnablePaymentSupplierGrid($DashboardFactory->getEnablePaymentSupplierGrid());
        }

        if(isset($post['payment_from'])) {
            $DashboardFactory->setPaymentFrom($post['payment_from'][0]);
        }else{
            $DashboardFactory->setPaymentFrom($DashboardFactory->getPaymentFrom());
        }
        if(isset($post['payment_supplier_count'])) {
            $DashboardFactory->setPaymentSupplierCount($post['payment_supplier_count']);
        } else {
            $payment_supplier_count = ($DashboardFactory->getPaymentSupplierCount() > 0) ? $DashboardFactory->getPaymentSupplierCount() : 5;
            $DashboardFactory->setPaymentSupplierCount($payment_supplier_count);
        }

        $DashboardFactory->setCustomerId($customer->getId());
        $DashboardFactory->save();
        $this->messageManager->addSuccessMessage(__('Manage Dashbpard Saved Successfully'));
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
