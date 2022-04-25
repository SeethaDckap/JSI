<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Dashboard;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Epicor\Supplierconnect\Helper\Crondata;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Sendinstantemail.
 */
class Sendinstantemail extends \Epicor\Supplierconnect\Controller\Dashboard
{

    /**
     * CustomerSession.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * SupplierConnectHelper.
     *
     * @var \Epicor\Supplierconnect\Helper\Crondata
     */
    private $supplierConnectHelper;

    /**
     * LocaleData.
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;


    /**
     * Send Instant Email constructor.
     *
     * @param Context           $context               Context.
     * @param Session           $customerSession       CustomerSession.
     * @param ResolverInterface $localeResolver        LocaleResolver.
     * @param PageFactory       $resultPageFactory     ResultPageFactory.
     * @param LayoutFactory     $resultLayoutFactory   ResultLayoutFactory.
     * @param Crondata          $supplierConnectHelper SupplierConnectHelper.
     * @param TimezoneInterface $localeDate            LocaleDate.
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ResolverInterface $localeResolver,
        PageFactory $resultPageFactory,
        LayoutFactory $resultLayoutFactory,
        Crondata $supplierConnectHelper,
        TimezoneInterface $localeDate
    ) {
        $this->customerSession       = $customerSession;
        $this->supplierConnectHelper = $supplierConnectHelper;
        $this->localeDate            = $localeDate;

        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );

    }//end __construct()


    /**
     * Execute.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $data                  = $this->getRequest()->getPost();
        $rfqs_due_today_enable = (isset($data['rfqs_due_today_enable'])) ? 1: 0;
        $rfqs_due_this_week_enable   = (isset($data['rfqs_due_this_week_enable'])) ? 1: 0;
        $upcoming_rfqs_enable        = (isset($data['upcoming_rfqs_enable'])) ? 1: 0;
        $all_open_rfqs_enable        = (isset($data['all_open_rfqs_enable'])) ? 1: 0;
        $reminder_expiry_date_enable = (isset($data['reminder_expiry_date_enable'])) ? 1: 0;
        $all_overdue_rfqs_enable     = (isset($data['all_overdue_rfqs_enable'])) ? 1: 0;
        $reminder_expiry_date        = ($data['reminder_expiry_date']) ? $data['reminder_expiry_date'] : '0000-00-00';
        $email_reminder_enable       = (isset($data['email_reminder_enable'])) ? 1: 0;
        $order_open_po_enable        = (isset($data['order_open_po_enable'])) ? 1: 0;
        $order_po_line_enable        = (isset($data['order_po_line_enable'])) ? 1: 0;

        /** @var \Magento\Customer\Model\Session $customer CustomerSession. */
        $customer = $this->customerSession->getCustomer();
        if ($customer->isSupplier()) {
            $susdMessage = $this->supplierConnectHelper->checkSusdEnabled();
            if ($susdMessage) {
                $messages  = $this->supplierConnectHelper->sendSusdMessage($customer->getEccSupplierErpaccountId());
                $rfqsData  = $messages->getRfqs();
                $orderData = $messages->getPurchaseOrders();
                $emailSent = false;

                if ($rfqs_due_today_enable) {
                    $emailSent = true;
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $rfqsData->getDueToday(),
                        "Rfqs Due today",
                        $customer->getEmail()
                    );
                }

                if ($rfqs_due_this_week_enable) {
                    $emailSent = true;
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $rfqsData->getDueWeek(),
                        'Rfqs Due this week',
                        $customer->getEmail()
                    );
                }

                if ($upcoming_rfqs_enable) {
                    $emailSent = true;
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $rfqsData->getDueFuture(),
                        'Upcoming Rfqs',
                        $customer->getEmail()
                    );
                }

                if ($all_open_rfqs_enable) {
                    $emailSent = true;
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $rfqsData->getOpen(),
                        'All Open Rfqs',
                        $customer->getEmail()
                    );
                }

                if ($all_overdue_rfqs_enable) {
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $rfqsData->getOverDue(),
                        'All Overdue Rfqs',
                        $customer->getEmail()
                    );
                }

                if ($order_open_po_enable) {
                    $emailSent = true;
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $orderData->getOpen(),
                        'All Open POs',
                        $customer->getEmail()
                    );
                }

                if ($order_po_line_enable) {
                    $emailSent = true;
                    $this->supplierConnectHelper->sendRfqsReminder(
                        $orderData->getChanges(),
                        'PO Line / Release Changes',
                        $customer->getEmail()
                    );
                }

                if ($reminder_expiry_date_enable && $email_reminder_enable) {
                    $currentDate      = $this->localeDate->date()->format('Ymd');
                    $expiryDate       = $reminder_expiry_date;
                    $remainderSent    = $this->localeDate->date($expiryDate)->format('Ymd');
                    $currentTimestamp = strtotime($currentDate);
                    $intervalDate     = strtotime($remainderSent) - $currentTimestamp;
                    $days             = floor($intervalDate / 86400);

                    if ($days > 0) {
                        $emailSent = true;
                        $this->supplierConnectHelper->sendExpiryReminder(
                            $expiryDate,
                            $customer->getId(),
                            'Before 7 Days'
                        );
                    }
                }

                if ($emailSent === true) {
                    echo 'Email Sent';
                } else {
                    echo 'Email Not Sent';
                }
            } else {
                echo 'Email Not Sent';
            }//end if
        } else {
            echo 'Not Allowed';
        }//end if

    }//end execute()


}
