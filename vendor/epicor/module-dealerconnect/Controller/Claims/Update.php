<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class Update extends \Epicor\Dealerconnect\Controller\Claims
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
        $files = array();
        try {
            if ($newData = $this->getRequest()->getPost()) {
                $helper = $this->dealerconnectHelper;
                $erpAccountNum = $helper->getErpAccountNumber();
                $fileHelper = $this->commonFileHelper;
                $customer = $this->customerSession->getCustomer();
                $commHelper = $this->commHelper;
                $oldData = unserialize(base64_decode($newData['old_claim_data']));
                if($newData['old_claim_data'])
                unset($newData['old_claim_data']);
                $newData = $commHelper->sanitizeData($newData);
                $oldData = $commHelper->sanitizeData($oldData);
                $oldQuoteData = array();
                if (isset($newData['old_data']) && $newData['old_data'])
                {
                    $oldQuoteData = unserialize(base64_decode($newData['old_data']));
                    unset($newData['old_data']);
                    $oldQuoteData = $commHelper->sanitizeData($oldQuoteData);
                }    
                    
                $dclu = $this->dealerconnectMessageRequestDclu;
                if ($dclu->isActive() && $helper->getMessageType('DCLU')) {

                    $cFiles = $aFiles = $lFiles = array();

                    if (isset($newData['claimattachments'])) {
                        $cFiles = $fileHelper->processPageFiles('claimattachments', $newData);
                    }
                    
                    if (isset($newData['attachments'])) {
                        $aFiles = $fileHelper->processPageFiles('attachments', $newData);
                    }

                    if (isset($newData['lineattachments'])) {
                        $lFiles = $fileHelper->processPageFiles('lineattachments', $newData);
                    }
                    $newData['rfq_files'] = array_merge($aFiles, $lFiles);
                    $files = array_merge($cFiles, $aFiles, $lFiles);
                    $dclu->setAction('U');
                    $dclu->setCaseNumber($newData['case_number']);
                    $dclu->setOldData($oldData);
                    $dclu->setOldQuoteData($oldQuoteData);
                    $dclu->setNewData($newData);

                    if ($customer->isSalesRep()) {
                        $prpHelper = $this->salesRepPricingRuleProductHelper;
                        $failedProducts = $prpHelper->validateLinesForDiscountedPrices($newData['lines']);
                    } else {
                        $failedProducts = array();
                    }

                    if (count($failedProducts) == 0) {
                        if ($dclu->sendMessage()) {
                            $this->registry->register('claims_editable', true);

                            $claim = $dclu->getResults();

                            $helper->processDcluFilesSuccess($files, $claim);

                            $this->registry->register('dealer_connect_claim_details', $claim);
                            $claimDetails = array(
                                'erp_account' => $erpAccountNum,
                                'case_number' => $claim->getCaseNumber()
                            );
                            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($claimDetails)));
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
                                'headerMessage' => 'The following Claim has been Updated on '
                            );
                            $dclu->sendEmail($data);
                            $url = $this->_url->getUrl('*/*/details', array('claim' => $requested));
                            $this->messageManager->addSuccessMessage(__('Claim updated successfully'));
                            return $this->redirectTo($url);
                        } else {
                            $helper->processDcluFilesFail($files);
                            $error = __('Claim update request failed');
                        }
                    } elseif (count($failedProducts) == 1) {
                        $error = __('Product %1 has an invalid price', implode(', ', $failedProducts));
                    } else {
                        $error = __('Products %1 have an invalid price', implode(', ', $failedProducts));
                    }
                } else {
                    $error = __('RFQ update not available');
                }
            } else {
                $error = __('No Data Sent');
            }
        } catch (\Exception $ex) {
            $error = __('An error occurred, please try again');
        }
        
        if ($error) {
            $this->messageManager->addErrorMessage($error);
            session_write_close();
            $url = $this->_url->getUrl('*/*/index');
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
