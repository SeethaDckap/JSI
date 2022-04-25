<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Dashboard;

class ReminderSave extends \Epicor\Supplierconnect\Controller\Dashboard
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

    protected  $rfqsRemainderFactory;

    protected $customerSession;

    protected $supplierReminderFactory;

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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Epicor\Supplierconnect\Model\SupplierReminderFactory $supplierReminderFactory
        ) {
        $this->customerconnectMessageRequestCuad = $customerconnectMessageRequestCuad;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->customerSession = $customerSession;
        $this->supplierReminderFactory = $supplierReminderFactory;
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
        $data = $this->getRequest()->getPost();
        $rfqs_due_today_enable = (isset($data['rfqs_due_today_enable'])) ? 1: 0;
        $rfqs_due_this_week_enable = (isset($data['rfqs_due_this_week_enable'])) ? 1: 0;
        $upcoming_rfqs_enable = (isset($data['upcoming_rfqs_enable'])) ? 1: 0;
        $rfqs_upcoming_options = $data['rfqs_upcoming_options'][0];
        $all_open_rfqs_enable = (isset($data['all_open_rfqs_enable'])) ? 1: 0;
        $rfqs_open_options = $data['rfqs_open_options'][0];
        $reminder_expiry_date_enable = (isset($data['reminder_expiry_date_enable'])) ? 1: 0;
        $all_overdue_rfqs_enable = (isset($data['all_overdue_rfqs_enable'])) ? 1: 0;
        $reminder_expiry_date = ($data['reminder_expiry_date']) ? $data['reminder_expiry_date'] : "0000-00-00";
        $email_reminder_enable = (isset($data['email_reminder_enable'])) ? 1: 0;
        $all_overdue_rfqs_options = $data['all_overdue_rfqs_options'][0];
        $order_open_po_enable = (isset($data['order_open_po_enable'])) ? 1: 0;
        $order_po_line_enable = (isset($data['order_po_line_enable'])) ? 1: 0;
        $order_po_line_options = $data['order_po_line_options'][0];
        $order_open_po_options = $data['order_open_po_options'][0];


        $customer = $this->customerSession->getCustomer();
        $rfqsRemainderFactor = $this->supplierReminderFactory->create();
        $rfqsRemainderFactor->load($customer->getId(),'customer_id');
        if(empty($order_open_po_enable) && empty($order_po_line_enable) && empty($rfqs_due_today_enable) && empty($rfqs_due_this_week_enable) && empty($upcoming_rfqs_enable) && empty($all_open_rfqs_enable) && empty($reminder_expiry_date_enable) && empty($all_overdue_rfqs_enable) ) {
            $rfqsRemainderFactor->delete();
        } else {
            $rfqsRemainderFactor->setIsActive(1);
            $rfqsRemainderFactor->setRfqsDueTodayEnable($rfqs_due_today_enable);
            $rfqsRemainderFactor->setRfqsDueThisWeekEnable($rfqs_due_this_week_enable);
            $rfqsRemainderFactor->setUpcomingRfqsEnable($upcoming_rfqs_enable);
            $rfqsRemainderFactor->setRfqsUpcomingOptions($rfqs_upcoming_options);
            $rfqsRemainderFactor->setAllOpenRfqsEnable($all_open_rfqs_enable);
            $rfqsRemainderFactor->setRfqsOpenOptions($rfqs_open_options);
            $rfqsRemainderFactor->setReminderExpiryDateEnable($reminder_expiry_date_enable);
            $rfqsRemainderFactor->setAllOverdueRfqsEnable($all_overdue_rfqs_enable);
            $rfqsRemainderFactor->setAllOverdueRfqsOptions($all_overdue_rfqs_options);
            $rfqsRemainderFactor->setOrderOpenPoEnable($order_open_po_enable);
            $rfqsRemainderFactor->setOrderPoLineEnable($order_po_line_enable);
            $rfqsRemainderFactor->setOrderPoLineOptions($order_po_line_options);
            $rfqsRemainderFactor->setOrderOpenPoOptions($order_open_po_options);
            $rfqsRemainderFactor->setReminderExpiryDate($reminder_expiry_date);
            $rfqsRemainderFactor->setEmailReminderEnable($email_reminder_enable);
            $rfqsRemainderFactor->setCustomerId($customer->getId());
            $rfqsRemainderFactor->save();
        }
        $this->messageManager->addSuccessMessage(__('Manage Dashbpard Saved Successfully'));
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
