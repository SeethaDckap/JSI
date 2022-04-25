<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Crqs;

class Update extends \Epicor\SalesRep\Controller\Crqs
{
    
    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;
    
    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;
    
    /**
     * @var \Epicor\SalesRep\Helper\Pricing\Rule\Product
     */
    protected $salesRepPricingRuleProductHelper;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqu
     */
    protected $customerconnectMessageRequestCrqu;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    protected $messageManager;
    
    protected $_objectManager;
    
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    
    
    public function __construct( 
        \Epicor\SalesRep\Controller\Context $context,   
        \Magento\Framework\Url\EncoderInterface $urlEncoder,   
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper, 
        \Epicor\Common\Helper\File $commonFileHelper, 
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper, 
        \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu
    )
    {
        $this->customerconnectRfqHelper          = $customerconnectRfqHelper;
        $this->commonFileHelper                  = $commonFileHelper;
        $this->salesRepPricingRuleProductHelper  = $salesRepPricingRuleProductHelper;
        $this->commHelper                        = $context->getCommHelper();
        $this->customerconnectMessageRequestCrqu = $customerconnectMessageRequestCrqu;
        $this->registry                          = $context->getRegistry();
        $this->urlEncoder                        = $urlEncoder;
        $this->messageManager                    = $context->getMessageManager();
        $this->_objectManager                    = $context->getObjectManager();
        parent::__construct($context);
    }
    
    
    
    public function execute()
    {
        $files = array();
        
        $error = '';
        
        try {
            if ($newData = $this->getRequest()->getPost()) {
                
                $helper     = $this->customerconnectRfqHelper;
                /* @var $helper Epicor_Customerconnect_Helper_Rfq */
                $fileHelper = $this->commonFileHelper;
                /* @var $fileHelper Epicor_Common_Helper_File */
                $prpHelper  = $this->salesRepPricingRuleProductHelper;
                /* @var $prpHelper Epicor_SalesRep_Helper_Pricing_Rule_Product */
                $commHelper = $this->commHelper;
                /* @var $commHelper Epicor_Comm_Helper_Data */
                $oldData    = unserialize(base64_decode($newData['old_data']));
                unset($newData['old_data']);
                $newData = $commHelper->sanitizeData($newData);
                $oldData = $commHelper->sanitizeData($oldData);
                
                $crqu = $this->customerconnectMessageRequestCrqu;
                /* @var $crqu Epicor_Customerconnect_Model_Message_Request_Crqu */
                
                if ($helper->getMessageType('CRQU')) {
                    
                    $aFiles = array();
                    $lFiles = array();
                    
                    if (isset($newData['attachments'])) {
                        $aFiles = $fileHelper->processPageFiles('attachments', $newData);
                    }
                    
                    if (isset($newData['lineattachments'])) {
                        $lFiles = $fileHelper->processPageFiles('lineattachments', $newData);
                    }
                    
                    $files = array_merge($aFiles, $lFiles);
                    
                    $crqu->setAction('U');
                    $crqu->setQuoteNumber($newData['quote_number']);
                    $crqu->setQuoteSequence($newData['quote_sequence']);
                    $crqu->setOldData($oldData);
                    $crqu->setNewData($newData);
                    
                    $failedProducts = $prpHelper->validateLinesForDiscountedPrices($newData['lines']);
                    
                    if (count($failedProducts) == 0) {
                        if ($crqu->sendMessage()) {
                            $this->messageManager->addSuccessMessage(__('RFQ update request sent successfully'));
                            
                            $this->registry->register('rfqs_editable', true);
                            
                            $rfq = $crqu->getResults();
                            
                            $helper->processCrquFilesSuccess($files, $rfq);
                            
                            $this->registry->register('customer_connect_rfq_details', $rfq);
                        } else {
                            
                            $helper->processCrquFilesFail($files);
                            $error = __('RFQ update request failed');
                        }
                    } elseif (count($failedProducts) == 1) {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$error = __('Product %s has an invalid price', implode(', ', $failedProducts));
                        $error = __('Product %1 has an invalid price', implode(', ', $failedProducts));
                        //M1 > M2 Translation End
                    } else {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$error = __('Products %s have an invalid price', implode(', ', $failedProducts));
                        $error = __('Products %1 have an invalid price', implode(', ', $failedProducts));
                        //M1 > M2 Translation End
                    }
                } else {
                    $error = __('RFQ update not available');
                }
            } else {
                $error = __('No Data Sent');
            }
        }
        catch (Exception $ex) {
            $error = __('An error occurred, please try again');
            $this->logger->critical($ex);
        }
        
        if ($error) {
            $this->registry->register('rfq_error', $error);
            
            $this->getResponse()->setBody($this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Showerror')->toHtml());
        } else {
            
            $helper        = $this->customerconnectHelper;
            $erpAccountNum = $helper->getErpAccountNumber();
            
            $quoteDetails = array(
                'erp_account' => $erpAccountNum,
                'quote_number' => $rfq->getQuoteNumber(),
                'quote_sequence' => $rfq->getQuoteSequence()
            );
            
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($quoteDetails)));
            //$requested = $this->urlEncoder->encode($this->encryptor->encrypt($erpAccountNum . ']:[' . $rfq->getQuoteNumber() . ']:[' . $rfq->getQuoteSequence()));
            //M1 > M2 Translation Begin (Rule p2-4)
            //$url = Mage::getUrl('*/*/details', array('quote' => $requested));
            $url       = $this->_url->getUrl('*/*/details', array(
                'quote' => $requested
            ));
            //M1 > M2 Translation End
            $this->registry->register('rfq_redirect_url', $url);
            $response = array(
                'redirect' => $url,
                'error' => false
            );
            session_write_close();
            $this->getResponse()->setBody($this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Redirector')->toHtml());
        }
    }
    
}