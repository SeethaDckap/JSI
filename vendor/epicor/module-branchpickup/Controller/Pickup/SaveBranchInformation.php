<?php
namespace Epicor\BranchPickup\Controller\Pickup;

use \Magento\Checkout\Model\Session as CheckoutSession;

class SaveBranchInformation extends \Epicor\BranchPickup\Controller\Pickup
{

/* Save Branch pickup location in checkout page */

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    
    /** @var CheckoutSession */
    protected $checkoutSession;    
    
    
    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;    
    
    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;    
    
    
    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;   
      /*
     * @var \Epicor\SalesRep\Helper\Checkout
     */
    protected $salesrepHelper; 

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Pay\Helper\Data
     */
    private $payHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        CheckoutSession $checkoutSession,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
         \Epicor\SalesRep\Helper\Checkout  $salesrepHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Epicor\Pay\Helper\Data $payHelper
    ) {
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->response = $context;
        $this->checkoutSession = $checkoutSession;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->customerSession = $customerSession;
        $this->salesrepHelper = $salesrepHelper;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->registry = $registry;
        $this->payHelper = $payHelper;
        $this->eventManager = $context->getEventManager();
        parent::__construct(
            $context,$branchPickupHelper,$customerSession
        );
    }


    public function execute()
    {                        
        try {
            $locationCode = $this->_request->getParam('locationcode');
            $helperBranchLocation = $this->branchPickupBranchpickupHelper;
            /* @var $helper Epicor_BranchPickup_Helper_Branchpickup */
            $payload = array();
            if (!$this->customerSession->isLoggedIn()) {
                $payload['email'] = $this->_request->getParam('email');
                $payload['firstname'] = $this->_request->getParam('firstname');
                $payload['lastname'] = $this->_request->getParam('lastname');
            }
            $payload['ecc_customer_order_ref'] = $this->_request->getParam('ecc_customer_order_ref');
            $payload['ecc_tax_exempt_reference'] = $this->_request->getParam('ecc_tax_exempt_reference');
            $payload['ecc_ship_status_erpcode'] = $this->_request->getParam('ecc_ship_status_erpcode') ?: "";
            $result = $helperBranchLocation->saveShippingInQuote($locationCode, $payload);
            $helper = $this->branchPickupHelper;
            /* @var $helper Epicor_BranchPickup_Helper_Data */

            $quote = $this->checkoutSession->getQuote();

            $orderRef = $this->_request->getParam('ecc_customer_order_ref');
            $quote->setEccCustomerOrderRef($orderRef);

            $taxRef = $this->_request->getParam('ecc_tax_exempt_reference');
            $quote->setEccTaxExemptReference($taxRef);

            $dda = $this->_request->getParam('ecc_required_date');
            if ($dda) {
                $quote->setEccRequiredDate($dda);
                $quote->setEccIsDdaDate(1);
            } else {
                $quote->setEccRequiredDate('');
                $quote->setEccIsDdaDate(0);
            }
            $customer = $this->customerSession->getCustomer();
            if ($customer->isSalesRep()) {
                if ($this->salesrepHelper->isEnabled() &&
                        $this->scopeConfig->isSetFlag('epicor_salesrep/checkout/choose_contact_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                ) {

                    $salesrep_contact = $this->_request->getParam('salesrep_contact');
                    $salesRepInfo = '';
                    $salesRepCustomerId = '';

                    if ($salesrep_contact) {
                        $salesRepInfo = base64_decode($salesrep_contact);
                        $salesRepData = unserialize($salesRepInfo);
                        /* @var $helper Epicor_Comm_Helper_Data */

                        $erpAccount = $this->commHelper->getErpAccountInfo();

                        if (!empty($salesRepData['ecc_login_id'])) {
                            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
                            $collection->addAttributeToFilter('ecc_contact_code', $salesRepData['contact_code']);
                            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccount->getId());
                            $collection->addFieldToFilter('website_id', $this->storeManager->getStore()->getWebsiteId());
                            $customer = $collection->getFirstItem();
                            $salesRepCustomerId = $customer->getId();
                        }
                    }

                    $customerSession = $this->customerSession;
                    /* @var $customerSession Mage_Customer_Model_Session */

                    $customer = $customerSession->getCustomer();
                    /* @var $customer Epicor_Comm_Model_Customer */

                    $quote->setEccSalesrepCustomerId($customer->getId());
                    $quote->setEccSalesrepChosenCustomerId($salesRepCustomerId);
                    $quote->setEccSalesrepChosenCustomerInfo($salesRepInfo);
                } else {

                    $quote->setEccSalesrepCustomerId($customer->getId());
                    $quote->setEccSalesrepChosenCustomerId('');
                    $quote->setEccSalesrepChosenCustomerInfo('');
                }
            }

            $quote->save();

            $selectBranch = $helper->selectBranchPickup($locationCode);

            $cartId = $this->checkoutSession->getQuote()->getId();
            $paymentDetails = $this->paymentDetailsFactory->create();
            $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
            $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
            foreach ($this->paymentMethodManagement->getList($cartId) as $paymentMethod) {
                $paymentMethods['payment_methods'][] = [
                    'code' => $paymentMethod->getCode(),
                    'title' => $paymentMethod->getTitle()
                ];
            }
            
            //return $paymentDetails;
            $paymentMethods['totals'] = $this->getTotalsData();
            $creditCheck = $this->registry->registry('credit_check');
            if ($creditCheck) {
                $creditCheckMsg = $this->payHelper->getCreditCheckMessage();
                if (is_string($creditCheckMsg)) {
                    $paymentMethods['extension_attributes']['ecc_pay_error'] = $creditCheckMsg;
                }
            }

            //event use for order approval message display in to payment page
            $extensionAttributes = new \Magento\Framework\DataObject();
            $this->eventManager->dispatch('ecc_branch_pickup_information_save_after',
                array(
                    'quote' => $quote,
                    'extension_attributes' => $extensionAttributes,
                )
            );

            $orderApprovalRequire
                = $extensionAttributes->getData("is_approval_require");
            if ($orderApprovalRequire) {
                $paymentMethods['extension_attributes']['is_approval_require']
                    = $orderApprovalRequire;
            }

            $this->response->getResponse()
                ->setHeader('Content-type', 'application/json');
            $this->response->getResponse()
                ->setBody(json_encode($paymentMethods));
            
        } catch (\Exception $ex) {
            $traceParam = [
                "message" => $ex->getMessage(),
                "trace" => $ex->getTraceAsString()
            ];

            $this->response->getResponse()->setHeader('Content-type', 'application/json');
            $this->response->getResponse()->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            $this->response->getResponse()->setBody(json_encode($traceParam));
        }
    }
    
    private function getTotalsData()
    {
        /** @var \Magento\Quote\Api\Data\TotalsInterface $totals */
        $totals = $this->cartTotalsRepository->get($this->checkoutSession->getQuote()->getId());
        $items = [];
        /** @var  \Magento\Quote\Model\Cart\Totals\Item $item */
        foreach ($totals->getItems() as $item) {
            $items[] = $item->__toArray();
        }
        $totalSegmentsData = [];
        /** @var \Magento\Quote\Model\Cart\TotalSegment $totalSegment */
        foreach ($totals->getTotalSegments() as $totalSegment) {
            $totalSegmentArray = $totalSegment->toArray();
            if (is_object($totalSegment->getExtensionAttributes())) {
                $totalSegmentArray['extension_attributes'] = $totalSegment->getExtensionAttributes()->__toArray();
            }
            $totalSegmentsData[] = $totalSegmentArray;
        }
        $totals->setItems($items);
        $totals->setTotalSegments($totalSegmentsData);
        $totalsArray = $totals->toArray();
        if (is_object($totals->getExtensionAttributes())) {
            $totalsArray['extension_attributes'] = $totals->getExtensionAttributes()->__toArray();
        }
        return $totalsArray;
    }    

    }
