<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval\Email\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Epicor\OrderApproval\Model\OrderHistoryManagement;
use Epicor\OrderApproval\Model\ErpManagement;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Class ApprovalSender
 *
 * @package Epicor\OrderApproval\Model\Approval\Email\Sender
 */
class ApproverSender
{

    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_ENABLED = 'ecc_order_approval/approver_email/enabled';
    const XML_PATH_EMAIL_COPY_METHOD = 'ecc_order_approval/approver_email/copy_method';
    const XML_PATH_EMAIL_COPY_TO = 'ecc_order_approval/approver_email/copy_to';
    const XML_PATH_EMAIL_IDENTITY = 'ecc_order_approval/approver_email/identity';
    const XML_PATH_EMAIL_TEMPLATE = 'ecc_order_approval/approver_email/template';

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StateInterface
     */
    private $translationStateInterface;

    /**
     * @var Renderer
     */
    private $addressRenderer;

    /**
     * @var OrderHistoryManagement
     */
    private $orderHistoryManagement;

    /**
     * @var ErpManagement
     */
    private $erpManagement;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var Store
     */
    protected $store;

    /**
     * ApproverSender constructor.
     *
     * @param Renderer                  $addressRenderer
     * @param PaymentHelper             $paymentHelper
     * @param TransportBuilder          $transportBuilder
     * @param ScopeConfigInterface      $scopeConfig
     * @param StoreManagerInterface     $storeManager
     * @param StateInterface            $translationStateInterface
     * @param OrderHistoryManagement    $orderHistoryManagement
     * @param ErpManagement             $erpManagement
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param \Psr\Log\LoggerInterface  $logger
     */
    public function __construct(
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        StateInterface $translationStateInterface,
        OrderHistoryManagement $orderHistoryManagement,
        ErpManagement $erpManagement,
        CustomerCollectionFactory $customerCollectionFactory,
        \Magento\Store\Model\Store $store,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->translationStateInterface = $translationStateInterface;
        $this->addressRenderer = $addressRenderer;
        $this->orderHistoryManagement = $orderHistoryManagement;
        $this->erpManagement = $erpManagement;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->store = $store;
    }

    /**
     * Sends order approval email to the Approver.
     *
     * @param Order $order
     *
     * @return false
     */
    public function send(Order $order)
    {
        if(!$this->isEnabled()) {
            return false;
        }

        try {
            $groupId
                = $this->orderHistoryManagement->getPendingGroupIdByOrderId($order->getId());
            if ( ! $groupId) {
                // no pending approval something wrong or order is stuck some where.
                return false;
            }

            $approversEmails = $this->getFromEmails($groupId);
            if ( ! count($approversEmails)) {
                return false;
            }

            //get customer email hear
            $this->transportBuilder->setTemplateIdentifier($this->getTemplateId())
                ->setTemplateOptions($this->getTemplateOptions())
                ->setTemplateVars($this->prepareTemplateVars($order))
                ->setFromByScope(
                    $this->scopeConfig->getValue(self::XML_PATH_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    $this->storeManager->getStore()->getId()
                )
                ->addTo($approversEmails);

            $copyTo = $this->getEmailCopyTo();
            if ( ! empty($copyTo) && $this->getCopyMethod() == 'bcc') {
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->translationStateInterface->resume(true);
        } catch (\Exception $e) {
            $this->translationStateInterface->resume(true);
        }
    }

    public function getTemplateId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Prepare email template with variables.
     *
     * @param Order $order
     *
     * @return array|mixed|null
     */
    protected function prepareTemplateVars(Order $order)
    {
        $transport = [
            'order'                    => $order,
            'billing'                  => $order->getBillingAddress(),
            'payment_html'             => $this->getPaymentHtml($order),
            'store'                    => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
            'created_at_formatted'     => $order->getCreatedAtFormatted(2),
            'order_data'               => [
                'customer_name'         => $order->getCustomerName(),
                'is_not_virtual'        => $order->getIsNotVirtual(),
                'email_customer_note'   => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel(),
            ],
        ];

        $transportObject = new DataObject($transport);

        return $transportObject->getData();
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     *
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Template options.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getTemplateOptions()
    {
        return [
            'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId(),
        ];
    }

    /**
     * Render shipping address into html.
     *
     * @param Order $order
     *
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(),
                'html');
    }

    /**
     * Render billing address into html.
     *
     * @param Order $order
     *
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format(
            $order->getBillingAddress(),
            'html'
        );
    }

    /**
     * Collect ALl Approver email address.
     *
     * @param $groupId
     *
     * @return array
     */
    public function getFromEmails($groupId)
    {
        $emails = array();
        $idErpIds = false;
        $erpIds = $this->getErpIds($groupId);
        if (count($erpIds) > 0) {
            $idErpIds = true;
        }
        //get customer
        $customers = $this->getGroupCustomer($groupId, $idErpIds);
        foreach ($customers as $item) {
            $emails[] = $item->getEmail();
        }

        //get ERP mapped Customers
        $erpIds = $this->getErpIds($groupId);
        if (count($erpIds) > 0) {
            $customerErpIds = [];
            foreach ($customers as $customersItem) {
                $customerErpIds[]
                    = $customersItem->getData("customer_erp_account");
            }

            $diffIds = array_diff($erpIds, $customerErpIds);
            if (count($diffIds) > 0) {
                $erpCustomers = $this->getCustomerIdByErpId($diffIds);
                foreach ($erpCustomers as $erpItems) {
                    $emails[] = $erpItems->getEmail();
                }
            }
        }
        
        return $emails;
    }

    /**
     * Approval get mapped erp account.
     *
     * @param $groupId
     *
     * @return array
     */
    public function getErpIds($groupId)
    {
        $erpIds = [];
        $items = $this->erpManagement->getErpAccounts($groupId);
        if (count($items) > 0) {
            foreach ($items as $item) {
                $erpIds[] = $item->getErpAccountId();
            }
        }

        return $erpIds;
    }

    /**
     * Get Approval customer by group ID.
     *
     * @param $groupId
     * @param $idErpIds
     *
     * @return DataObject[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroupCustomer($groupId, $idErpIds = false)
    {
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->addAttributeToSelect('entity_id');
        $customerCollection->addAttributeToSelect('email');

        if ($idErpIds) {
            //ecc_customer_erp_account
            $ecea = $customerCollection->getTable('ecc_customer_erp_account');
            $customerCollection->joinTable(array('ecea' => $ecea),
                'customer_id = entity_id',
                array('customer_erp_account' => 'erp_account_id'),
                null,
                'left');
            $customerCollection->addAttributeToSelect('customer_erp_account');

            //ecc_erp_account
            $eea = $customerCollection->getTable('ecc_erp_account');
            $customerCollection->joinTable(array('eea' => $eea),
                'entity_id = customer_erp_account',
                array('erp_short_code' => 'short_code'),
                null,
                'left'
            );

            //ecc_approval_group_erp_account
            $eagea
                = $customerCollection->getTable('ecc_approval_group_erp_account');
            $customerCollection->joinTable(array('eagea' => $eagea),
                'erp_account_id = customer_erp_account',
                array('approval_group_id' => 'group_id'),
                null,
                'left');
        }

        //ecc_approval_group_customer
        $eagc = $customerCollection->getTable('ecc_approval_group_customer');
        $customerCollection->joinTable(array('eagc' => $eagc),
            'customer_id = entity_id',
            array('approval_customer_group_id' => 'group_id'),
            null,
            'left'
        );

        if ($idErpIds) {
            $customerCollection->addFieldToFilter('approval_group_id',
                array('eq' => $groupId));
        }

        $customerCollection->addFieldToFilter('approval_customer_group_id',
            array('eq' => $groupId));

        return $customerCollection->getItems();
    }

    /**
     * @param $erpIds
     *
     * @return DataObject[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdByErpId($erpIds)
    {
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->addAttributeToSelect('entity_id');
        $customerCollection->addAttributeToSelect('email');

        $ecea
            = $customerCollection->getTable('ecc_customer_erp_account');
        $customerCollection->joinTable(array('ecea' => $ecea),
            'customer_id = entity_id', array(
                'erp_account_id' => 'erp_account_id',
            ), null, 'left');

        $customerCollection->addFieldToFilter('erp_account_id',
            array('in' => $erpIds));

        return $customerCollection->getItems();
    }

    /**
     * Is email enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $data = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_COPY_TO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($data)) {
            return array_map('trim', explode(',', $data));
        }
        return false;
    }

    /**
     * Return copy method
     *
     * @return mixed
     */
    public function getCopyMethod()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_COPY_METHOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

}
