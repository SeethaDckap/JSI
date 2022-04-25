<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ Details page buttons
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Buttons extends \Epicor\AccessRight\Block\Template
{

    const FRONTEND_RESOURCE_CREATE = "Epicor_Customerconnect::customerconnect_account_rfqs_create";

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customerconnect::customerconnect_account_rfqs_edit';

    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Epicor_Customerconnect::customerconnect_account_rfqs_confirmrejects';

    private $_status;

    /**
     *
     * @var \Epicor\Quotes\Model\Quote
     */
    private $_eccQuote;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\AccessRight\Model\Authorization $authorization,
        array $data = []
    )
    {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->storeManager = $context->getStoreManager();
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    private function _getRfqStatus()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        if (!$this->_status && $rfq) {
            $helper = $this->customerconnectMessagingHelper;
            $this->_status = $helper->getErpquoteStatusDescription($rfq->getQuoteStatus(), '', 'state');
        }

        return $this->_status;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/buttons.phtml');
    }

    public function showConfirm()
    {
        if ($this->registry->registry('hide_all_buttons')) {
            return false;
        }

        $access = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT);

        if ($access) {
            $helper = $this->commMessagingHelper;
            $access = $helper->isMessageEnabled('customerconnect', 'crqc');

            $erpAccount = $helper->getErpAccountInfo();
            $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

            if ($access && !$currencyCode) {
                $access = false;
            }
        }

        return ($access && $this->confirmRejectStatusCheck());
    }

    public function showDuplicate()
    {
        if ($this->registry->registry('hide_all_buttons')) {
            return false;
        }

        $show = $this->_isCreateAllowed();

        if ($show) {
            $helper = $this->commMessagingHelper;
            $show = $helper->isMessageEnabled('customerconnect', 'crqu');

            $erpAccount = $helper->getErpAccountInfo();
            $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

            if ($show && !$currencyCode) {
                $show = false;
            }
        }
        $action = $this->getRequest()->getActionName();
        if ($action == 'new' || $action == 'duplicate') {
            $show = false;
        }

        return $show;
    }

    public function showReject()
    {
        if ($this->registry->registry('hide_all_buttons')) {
            return false;
        }

        $access = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT);

        if ($access) {
            $helper = $this->commMessagingHelper;
            $access = $helper->isMessageEnabled('customerconnect', 'crqc');
        }

        return ($access && $this->confirmRejectStatusCheck());
    }

    private function confirmRejectStatusCheck()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');

        $status = $this->_getRfqStatus();
        $_allowedStatuses = !$this->customerconnectMessagingHelper->confirmRejectQuoteStatus($status, $rfq);
        return ($rfq && $_allowedStatuses);
    }

    public function showCheckoutButton()
    {
        if ($this->registry->registry('hide_all_buttons')) {
            return false;
        }
        $rfq = $this->registry->registry('customer_connect_rfq_details');

        $status = $this->_getRfqStatus();

        $helper = $this->commonAccessHelper;

        $show = false;

        if ($rfq && $rfq->getQuoteEntered() == 'Y' && $status == \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_AWAITING) {

            if ($helper->customerHasAccess('Epicor_Quotes', 'Manage', 'accept', '', 'Access')) {

                $quote = $this->getEccQuote();

                if ($quote->getId() && $quote->isAcceptable()) {
                    $show = true;
                }
            }
        }

        return $show;
    }

    private function getEccQuote()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        if ($rfq && is_null($this->_eccQuote)) {
            $quoteNumber = $rfq->getQuoteNumber();

            $this->_eccQuote = $this->quotesQuoteFactory->create();
            $this->_eccQuote->load($quoteNumber, 'quote_number');
        }

        return $this->_eccQuote;
    }

    public function getDuplicateUrl()
    {
        $parms = $this->getRequest()->getParams();
        if (array_key_exists('rfq_serialize_data', $parms)) {          // if duplicate_url is specified, quote will not be and vice versa 
            $duplicateUrl = substr($parms['rfq_serialize_data'], strpos($parms['rfq_serialize_data'], "=") + 1);  
            $realUrl = current(explode('quote_address', $duplicateUrl));
            return urldecode($realUrl);
        } else {
            $params = array(
                'quote' => $this->getRequest()->getParam('quote')
            );
            if($this->getRequest()->getModuleName() === "dealerconnect"){
                return $this->getUrl('dealerconnect/quotes/duplicate', $params);
            }
            return $this->getUrl('customerconnect/rfqs/duplicate', $params);
        }
    }

    public function getConfirmUrl()
    {
        return $this->getUrl('customerconnect/rfqs/confirm');
    }

    public function getRejectUrl()
    {
        return $this->getUrl('customerconnect/rfqs/reject');
    }

    public function getCheckoutUrl()
    {
        $quote = $this->getEccQuote();

        return $this->getUrl('quotes/manage/accept/', array('id' => $quote->getId()));
    }

    //M1 > M2 Translation Begin (Rule p2-8)

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    //M1 > M2 Translation End

}
