<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class Add extends \Epicor\Dealerconnect\Controller\Claims
{

    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $dealerconnectHelper;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Dclu
     */
    protected $dealerconnectMessageRequestDclu;

    /**
     * @var \Epicor\SalesRep\Helper\Pricing\Rule\Product
     */
    protected $salesRepPricingRuleProductHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * @var type \Magento\Backend\Model\View\Result\RedirectFactory 
     */
    protected $resultRedirectFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory, 
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Framework\Locale\ResolverInterface $localeResolver, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, 
        \Magento\Framework\Registry $registry, 
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper, 
        \Magento\Framework\App\Request\Http $request, 
        \Epicor\Dealerconnect\Model\Message\Request\Dcld $dealerconnectMessageRequestDcld, 
        \Magento\Framework\Session\Generic $generic, 
        \Epicor\Common\Helper\Access $commonAccessHelper, 
        \Epicor\Comm\Helper\Messaging $commMessagingHelper, 
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper, 
        \Epicor\Comm\Helper\Product $commProductHelper, 
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory, 
        \Magento\Framework\Url\DecoderInterface $urlDecoder, 
        \Magento\Framework\Encryption\EncryptorInterface $encryptor, 
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper, 
        \Epicor\Common\Helper\File $commonFileHelper, 
        \Epicor\Comm\Helper\Data $commHelper, 
        \Epicor\Dealerconnect\Model\Message\Request\Dclu $dealerconnectMessageRequestDclu, 
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper, 
        \Magento\Framework\Url\EncoderInterface $urlEncoder, 
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid
    ) {
        $this->dealerconnectHelper = $dealerconnectHelper;
        $this->commonFileHelper = $commonFileHelper;
        $this->commHelper = $commHelper;
        $this->dealerconnectMessageRequestDclu = $dealerconnectMessageRequestDclu;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        $this->urlEncoder = $urlEncoder;
        $this->jsonHelper = $jsonHelper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct(
            $context, 
            $customerSession, 
            $localeResolver, 
            $resultPageFactory, 
            $resultLayoutFactory, 
            $registry, 
            $dealerconnectHelper, 
            $request, 
            $dealerconnectMessageRequestDcld, 
            $generic, $commonAccessHelper, 
            $commMessagingHelper, 
            $commConfiguratorHelper, 
            $commProductHelper, 
            $catalogProductFactory, 
            $storeManager, 
            $commMessageRequestCdmFactory, 
            $scopeConfig, 
            $commonXmlvarienFactory, 
            $urlDecoder, 
            $encryptor,
            $dcMessageRequestDeid
        );
    }

    public function execute() 
    {
        $error = false;
        try {
            if ($newData = $this->getRequest()->getPost()) {    
                $helper = $this->dealerconnectHelper;
                $erpAccountNum = $helper->getErpAccountNumber();
                $fileHelper = $this->commonFileHelper;
                /** @var \Epicor\Comm\Model\Customer $customer */
                $customer = $this->customerSession->getCustomer();
                $commHelper = $this->commHelper;

                if(isset($newData['old_claim_data']) && (isset($newData['claim_new']) && $newData['claim_new'] !== '1')){
                   $oldData = unserialize(base64_decode($newData['old_claim_data']));
                   unset($newData['old_claim_data']);
                   $oldData = $commHelper->sanitizeData($oldData);
                }
                $newData = $commHelper->sanitizeData($newData);
                $dclu = $this->dealerconnectMessageRequestDclu;
                $duplicate = isset($newData['is_duplicate']) ? true : false;
                if ($dclu->isActive() && $helper->getMessageType('DCLU')) {

                    if ($customer->isSalesRep()) {
                        $prpHelper = $this->salesRepPricingRuleProductHelper;
                        $failedProducts = $prpHelper->validateLinesForDiscountedPrices($newData['lines']);
                    } else {
                        $failedProducts = array();
                    }

                    if (count($failedProducts) == 0) {

                        $cFiles = $aFiles = $lFiles = array();
                        
                        if (isset($newData['claimattachments'])) {
                            $cFiles = $fileHelper->processPageFiles('claimattachments', $newData, $duplicate, false);
                        }
                        
                        if (isset($newData['attachments'])) {
                            $aFiles = $fileHelper->processPageFiles('attachments', $newData, $duplicate, false);
                        }

                        if (isset($newData['lineattachments'])) {
                            $lFiles = $fileHelper->processPageFiles('lineattachments', $newData, $duplicate, false);
                        }

//                        if ($this->registry->registry('download_erp_files')) {
//                            $this->messageManager->addSuccessMessage(__('New RFQ request sent. There will be a delay while attachments are synced'));
//                            $connection = new \Zend_Http_Client();
//                            $adapter = new \Zend_Http_Client_Adapter_Curl();
//
//                            try {
//                                //M1 > M2 Translation Begin (Rule p2-4)
//                                //$connection->setUri(Mage::getUrl('epicor_comm/message/crqu', array('_store' => $this->storeManager->getStore()->getId())));
//                                $connection->setUri($this->_url->getUrl('epicor_comm/message/crqu', array('_store' => $this->storeManager->getStore()->getId())));
//                                //M1 > M2 Translation End
//
//                                $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 0);
//                                $adapter->setCurlOption(CURLOPT_POST, 1);
//
//                                $adapter->setCurlOption(CURLOPT_USERAGENT, 'api');
//                                $adapter->setCurlOption(CURLOPT_TIMEOUT, 1);
//                                $adapter->setCurlOption(CURLOPT_HEADER, 0);
//                                $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, false);
//                                $adapter->setCurlOption(CURLOPT_FORBID_REUSE, true);
//                                $adapter->setCurlOption(CURLOPT_CONNECTTIMEOUT, 1);
//                                $adapter->setCurlOption(CURLOPT_DNS_CACHE_TIMEOUT, 10);
//                                $adapter->setCurlOption(CURLOPT_FRESH_CONNECT, true);
//
//                                $helper = $this->customerconnectHelper;
//
//                                $newData['account_number'] = $helper->getErpAccountNumber();
//
//                                $connection->setParameterPost('data', base64_encode(serialize($newData)));
//                                $connection->setAdapter($adapter);
//                                $connection->request(\Zend_Http_Client::POST);
//                            } catch (\Exception $e) {
//
//                            }
//                            //M1 > M2 Translation Begin (Rule p2-4)
//                            //$url = Mage::getUrl('*/*/index');
//                            $url = $this->_url->getUrl('*/*/index');
//                            //M1 > M2 Translation End
//
//                            $this->registry->register('rfq_redirect_url', $url);
//                            $response = array(
//                                'redirect' => $url,
//                                'error' => false
//                            );
//                            session_write_close();
//                            $this->getResponse()->setBody(
//                                $this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Redirector')->toHtml()
//                            );
//
//                        } else {
                            $files = array_merge($cFiles, $aFiles, $lFiles);

                            $dclu->setAction('A');
                            $dclu->setCaseNumber('');
                            $dclu->setQuoteSequence('');
                            $dclu->setOldData(array());
                            $dclu->setNewData($newData);

                            if ($dclu->sendMessage()) {
                                $this->messageManager->addSuccessMessage(__('New Claim request sent successfully'));

                                $accessHelper = $this->commonAccessHelper;

                                $access = $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'update', '', 'Access');
                                $this->registry->register('rfqs_editable', $access);

                                $claim = $dclu->getResults();

                                $helper->processDcluFilesSuccess($files, $claim);

                                $this->registry->register('dealer_connect_claim_details', $claim);
                                $serialNumber = $comment = '';
                                if ($claim->getSerialNumbers()) {
                                    $serialNumbers = $claim->getSerialNumbers()->getasarraySerialNumber();
                                    $serialNumber = implode(', ', $serialNumbers);
                                }
                                if (isset($newData['claim_comment']) && $newData['claim_comment'] != "") {
                                    $comment = $newData['claim_comment'];
                                }
                                $data = array(
                                    'caseNumber' => $claim->getCaseNumber(),
                                    'locationNumber' => $claim->getLocationNumber(),
                                    'serialNumbers' => $serialNumber,
                                    'identificationNumbers' => $claim->getIdentificationNumber(),
                                    'comment' => $comment,
                                    'dealerName' =>$this->customerSession->getCustomer()->getName(),
                                    'dealerEmail' =>$this->customerSession->getCustomer()->getEmail(),
                                    'headerMessage' => 'The following Claim has been Created on '
                                );
                                $dclu->sendEmail($data);
                                $claimDetails = array(
                                    'erp_account' => $erpAccountNum,
                                    'case_number' => $claim->getCaseNumber()
                                );
                                $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($claimDetails)));
                                $url = $this->_url->getUrl('*/*/details', array('claim' => $requested));
                                return $this->redirectTo($url);
                            } else {
                                $helper->processDcluFilesFail($files);
                                $error = __('Claim add request failed');
                            }
                    } elseif (count($failedProducts) == 1) {
                        $error = __('Product %1 has an invalid price', implode(', ', $failedProducts));
                    } else {
                        $error = __('Products %1 have an invalid price', implode(', ', $failedProducts));
                    }
                } else {
                    $error = __('Claim add not available');
                }
            } else {
                $error = __('No Data Sent');
            }
        } catch (\Exception $ex) {
            $error = __('An error occurred, please try again:' . $ex->getMessage());
        }
        
        if ($error) {
            $this->messageManager->addErrorMessage($error);
            $url = $this->_url->getUrl('*/*/index');
            return $this->redirectTo($url);
        } else {
            $claimDetails = array(
                'erp_account' => $erpAccountNum,
                'case_number' => $claim->getCaseNumber()
            );
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($claimDetails)));
            $url = $this->_url->getUrl('*/*/details', array('claim' => $requested));
            session_write_close();
            return $this->redirectTo($url);
        }
    }
    
    protected function redirectTo($url) 
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($url);
        return $resultRedirect;
    }

}
