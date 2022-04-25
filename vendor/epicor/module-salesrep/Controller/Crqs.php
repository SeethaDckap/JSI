<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller;


/**
 * Account controller
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
abstract class Crqs extends \Epicor\SalesRep\Controller\Generic
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqd
     */
    protected $customerconnectMessageRequestCrqd;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;
    /*
     * @var \Magento\Framework\Unserialize\Unserialize $unserialize
     */
    protected $unserialize;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
     
    public function __construct(
          \Epicor\SalesRep\Controller\Context $context
    ) {
        $this->urlDecoder = $context->getUrlDecoder();
        $this->unserialize = $context->getUnserialize();
        $this->encryptor = $context->getEncryptor();
        $this->customerSession = $context->getCustomerSession();
        $this->registry = $context->getRegistry();
        $this->customerconnectHelper =$context->getCustomerconnectHelper();
        $this->request = $context->getRequest();
        $this->customerconnectMessageRequestCrqd = $context->getCustomerconnectMessageRequestCrqd();
        $this->commonAccessHelper = $context->getCommonAccessHelper();
        $this->customerconnectMessagingHelper = $context->getCustomerconnectMessagingHelper();
        $this->commMessagingHelper = $context->getCommMessagingHelper();
        $this->commHelper = $context->getCommHelper();
        $this->frameworkHelperDataHelper = $context->getFrameworkHelperDataHelper();
        $this->_localeResolver = $context->getLocalResolver();
          parent::__construct(
            $context
        );
        
    }
    protected function _loadRfq()
    {
        $newRfq = $this->registry->registry('customer_connect_rfq_details');
        /* @var $session Mage_Core_Model_Session */
        $loaded = false;

        if ($newRfq) {
            $loaded = true;
        }

        if (!$this->registry->registry('customer_connect_rfq_details')) {

            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Data */
            $quote = $this->urlDecoder->decode($this->request->getParam('quote'));
            $quoteDetails = $this->unserialize->unserialize($this->encryptor->decrypt($quote));
            if (isset($quoteDetails['return'])) {
                $this->registry->register('rfq_return_url', $quoteDetails['return']);
                unset($quoteDetails['return']);
            }

            $brand = $helper->getStoreBranding();

            // if brand company exists, check if brand and delimiter are already in the erp_account number, if not add them
            $accountNumber = $quoteDetails['erp_account'];
            if ($brand->getCompany()) {
                if (!strstr($quoteDetails['erp_account'], $brand->getCompany() . $helper->getUOMSeparator())) {
                    $accountNumber = $brand->getCompany() . $helper->getUOMSeparator() . $quoteDetails['erp_account'];
                }
            }
            $erpAccount = $helper->getErpAccountByAccountNumber($accountNumber);
            $this->registry->register('crq_erp_account', $erpAccount);
            if (
                count($quoteDetails) == 3 && !empty($quoteDetails['quote_number']) && array_key_exists('quote_sequence', $quoteDetails)
            ) {
                $crqd = $this->customerconnectMessageRequestCrqd;
                $messageTypeCheck = $crqd->getHelper('customerconnect/messaging')->getMessageType('CRQD');

                if ($crqd->isActive() && $messageTypeCheck) {

                    //M1 > M2 Translation Begin (Rule p2-6.4)
                    /*$crqd->setAccountNumber($quoteDetails['erp_account'])
                        ->setQuoteNumber($quoteDetails['quote_number'])
                        ->setQuoteSequence($quoteDetails['quote_sequence'])
                        ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));*/
                    $locale = $this->_localeResolver->getLocale();
                    $crqd->setAccountNumber($quoteDetails['erp_account'])
                        ->setQuoteNumber($quoteDetails['quote_number'])
                        ->setQuoteSequence($quoteDetails['quote_sequence'])
                        ->setLanguageCode($helper->getLanguageMapping($locale));
                    //M1 > M2 Translation End

                    if ($crqd->sendMessage()) {
                        $rfq = $crqd->getResults();
                        $this->registry->register('customer_connect_rfq_details', $rfq);
                        $loaded = true;
                    } else {
                        $this->messageManager->addErrorMessage(__('Failed to retrieve RFQ Details'));
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('ERROR - RFQ Details not available'));
                }
            } else {
               $this->messageManager->addErrorMessage(__('ERROR - Invalid RFQ Number'));
            }
        } else {
            $loaded = true;
        }

        if ($loaded) {
            $accessHelper = $this->commonAccessHelper;
            /* @var $helper Epicor_Common_Helper_Access */
            $editable = $accessHelper->customerHasAccess('Epicor_SalesRep', 'Crqs', 'update', '', 'Access');

            $helper = $this->customerconnectMessagingHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Messaging */
            $rfq = $this->registry->registry('customer_connect_rfq_details');
            $status = $helper->getErpquoteStatusDescription($rfq->getQuoteStatus(), '', 'state');

            if ($editable) {
                if ($status != \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_PENDING) {
                    $editable = false;
                }
            }

            $msgHelper = $this->commMessagingHelper;
            /* @var $msgHelper Epicor\Comm\Helper\Messaging */

            if ($editable && $rfq->getCurrencyCode() != $msgHelper->getCurrencyMapping()) {
                $editable = false;
            }

            $enabled = $msgHelper->isMessageEnabled('customerconnect', 'crqu');

            if ($enabled && $status == \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_AWAITING) {
                $this->registry->register('rfqs_editable_partial', true);
            }

            if (!$enabled) {
                $editable = false;
            }

            if ($erpAccount->getErpCode() !== $helper->getErpAccountNumber()) {
                $editable = false;
                $returnUrl = $this->frameworkHelperDataHelper->getCurrentBase64Url();
                //M1 > M2 Translation Begin (Rule p2-4)
                //$masqUrl = Mage::getUrl('epicor_comm/masquerade/masquerade', array('masquerade_as' => $erpAccount->getId(), 'return_url' => $returnUrl));
                $masqUrl = $this->_url->getUrl('epicor_comm/masquerade/masquerade', array('masquerade_as' => $erpAccount->getId(), 'return_url' => $returnUrl));
                //M1 > M2 Translation End
                $this->messageManager->addNoticeMessage(__('You are not masquerading as the ERP Account for this Quote'));

                $customerSession = $this->customerSession;
                /* @var $customerSession Mage_Customer_Model_Session */

                $customer = $customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */

                if ($customer->canMasqueradeAs($erpAccount->getId())) {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$session->addNotice($this->__('In order to make changes to this Quote, you must be masquerading as the correct ERP Account. %s', '<a href="' . $masqUrl . '">Start Masquerade Now</a>'));
                    $this->messageManager->addNotice(__('In order to make changes to this Quote, you must be masquerading as the correct ERP Account. %1', '<a href="' . $masqUrl . '">Start Masquerade Now</a>'));
                    //M1 > M2 Translation End
                } else {
                    $this->messageManager->addNoticeMessage(__('You are not allowed to masquerade as this ERP Account'));
                }
                $this->registry->register('hide_all_buttons', true);
            }

            $this->registry->register('rfqs_editable', $editable);
        }

        return $loaded;
    }
}
