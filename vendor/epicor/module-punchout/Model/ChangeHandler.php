<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Epicor\Comm\Model\Message\Request\Gor;
use Epicor\Punchout\Api\Data\Order\PurchaseOrderInterface;
use \Psr\Log\LoggerInterface;

/**
 * Quote to order.
 *
 */
class ChangeHandler
{

    const TEMPLATE_IDENTIFIER = 'ecc_punchout_order_email_template';
    const PRICE_ERROR         = 'Requested item(s) price/tax value might have changed.';
    const ORDER_SUBMIT_ERROR  = 'Unable to submit order to ERP. Will be retried for processing and intimated accordingly.';
    const ITEM_QTY_ERROR      = 'Requested item(s)/item quantities might have changed due to unavailability.';
    const TECHNICAL_ERROR     = 'Due to some technical error order request could not be processed.';
    const CUSTOMER_ERROR      = 'The requested Customer record  no longer exists.';
    const EMPTY_QUOTE_ERROR   = 'No item(s) requested are available.';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StateInterface
     */
    private $inlineTranslation ;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder ;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $changes = [];

    /**
     * @var array
     */
    private $emailDetails;


    /**
     * Constructor.
     *
     * @param StoreManagerInterface       $storeManager
     * @param StateInterface              $inlineTranslation
     * @param TransportBuilder            $transportBuilder
     * @param ScopeConfigInterface        $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface             $logger
     *
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->storeManager                = $storeManager;
        $this->inlineTranslation           = $inlineTranslation;
        $this->transportBuilder            = $transportBuilder;
        $this->scopeConfig                 = $scopeConfig;
        $this->customerRepositoryInterface = $customerRepository;
        $this->logger                      = $logger;

    }//end __construct()


    /**
     * Identify changes and send email.
     *
     * @param OrderInterface|null $order
     * @param PurchaseOrderInterface $orderInstance
     * @param mixed $message
     * @param $proceedWithIdentification
     *
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function handleChanges(?OrderInterface $order, PurchaseOrderInterface $orderInstance, $message, $proceedWithIdentification)
    {
        if ($proceedWithIdentification) {
            $this->identifyChanges($order, $orderInstance);
        }

        if (isset($message['exception'])) {
            $this->logger->error($message['exception']);
            unset($message['exception']);
        }

        $this->changes = array_merge($this->changes, $message);

        if (!empty($this->changes)) {
            $this->getEmailDetails($orderInstance);
            $this->sendEmail();
        }

    }//end handleChanges()


    /**
     * Send email.
     *
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmail()
    {
        $templateOptions = [
            'area'  => Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId(),
        ];
        $templateVars    = [
            'store'     => $this->storeManager->getStore(),
            'name'      => $this->emailDetails['name'],
            'messages'  => array_unique($this->changes),
            'po_number' => $this->emailDetails['po_number'],
        ];

        $setting = 'general';
        $from    = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name    = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from    = [
            'email' => $from,
            'name'  => $name,
        ];

        $this->inlineTranslation->suspend();
        $to        = $this->emailDetails['email'];
        $transport = $this->transportBuilder->setTemplateIdentifier(self::TEMPLATE_IDENTIFIER)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();

    }//end sendEmail()


    /**
     * Identify Changes.
     *
     * @param OrderInterface|null $order
     * @param PurchaseOrderInterface $orderInstance
     */
    public function identifyChanges(?OrderInterface $order, PurchaseOrderInterface $orderInstance)
    {
        if (!is_null($order) && $order->getId()) {
            //GOR not sent
            if ($order->getEccGorSent() !== Gor::GOR_STATUS_SENT) {
                $this->changes[] = self::ORDER_SUBMIT_ERROR;
                return;
            }

            //item count mismatch
            $orderItemsCount   = $order->getTotalItemCount();
            $requestItemsCount = count($orderInstance->getItemArray());


            if ($orderItemsCount != $requestItemsCount) {
                $this->changes[] = self::ITEM_QTY_ERROR;
            }

            //Price mismatch
            $requestTotals = json_decode($orderInstance->getTotals()[0], true);
            if (($order->getSubtotalInclTax() + $order->getBaseDiscountAmount()) != $requestTotals['totalInc'][0]) {
                $this->changes[] = self::PRICE_ERROR;
            }
        } else {
            $this->changes[] = self::TECHNICAL_ERROR;
        }

    }//end identifyChanges()


    /**
     * Get Details.
     *
     * @param PurchaseOrderInterface $orderInstance
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getEmailDetails(PurchaseOrderInterface $orderInstance)
    {
        $cus             = $this->customerRepositoryInterface->getById($orderInstance->getCustomerId());
        $email           = [$cus->getEmail()];
        $additionalEmail = $this->scopeConfig->getValue('epicor_punchout/general/send_email_copy', ScopeInterface::SCOPE_STORE);
        $additionalEmail = $additionalEmail ? explode(',', $additionalEmail) : [];
        $email           = array_map('trim', array_merge($email, $additionalEmail));


        $this->emailDetails = [
            'name'      => $cus->getFirstname(),
            'email'     => $email,
            'po_number' => $orderInstance->getOrderId()
        ];

    }//end getCustomer()


}//end class