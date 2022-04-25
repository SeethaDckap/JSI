<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Add extends \Epicor\Customerconnect\Controller\Rfqs {

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqu
     */
    protected $customerconnectMessageRequestCrqu;

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
    \Magento\Framework\App\Action\Context $context, \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\Registry $registry, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\App\Request\Http $request, \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd, \Magento\Framework\Session\Generic $generic, \Epicor\Common\Helper\Access $commonAccessHelper, \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Comm\Helper\Configurator $commConfiguratorHelper, \Epicor\Comm\Helper\Product $commProductHelper, \Magento\Catalog\Model\ProductFactory $catalogProductFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory, \Magento\Framework\Url\DecoderInterface $urlDecoder, \Magento\Framework\Encryption\EncryptorInterface $encryptor, \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper, \Epicor\Common\Helper\File $commonFileHelper, \Epicor\Comm\Helper\Data $commHelper, \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu, \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper, \Magento\Framework\Url\EncoderInterface $urlEncoder, \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->commonFileHelper = $commonFileHelper;
        $this->commHelper = $commHelper;
        $this->customerconnectMessageRequestCrqu = $customerconnectMessageRequestCrqu;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        $this->urlEncoder = $urlEncoder;
        $this->jsonHelper = $jsonHelper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory, $registry, $customerconnectHelper, $request, $customerconnectMessageRequestCrqd, $generic, $commonAccessHelper, $customerconnectMessagingHelper, $commMessagingHelper, $commConfiguratorHelper, $commProductHelper, $catalogProductFactory, $storeManager, $commMessageRequestCdmFactory, $scopeConfig, $commonXmlvarienFactory, $urlDecoder, $encryptor
        );
    }

    public function execute() {
        $error = false;
        $deliveryAddress = $this->getRequest()->getPost('delivery_address');
        $error = $this->getRequest()->getPost('delivery_address') ? $this->customerconnectHelper->addressValidate($deliveryAddress, true) : null;
        if (!isset($error)) {
            try {
                if ($newData = $this->getRequest()->getPost()) { 
                    $_post = $this->getRequest()->getPost();
                    $newDataUpdate = $this->getProcessedRfqData($_post['rfq_serialize_data']);                       
                    
                    $helper = $this->customerconnectRfqHelper;
                    $fileHelper = $this->commonFileHelper;
                    /** @var \Epicor\Comm\Model\Customer $customer */
                    $customer = $this->customerSession->getCustomer();
                    $commHelper = $this->commHelper;

                    if(isset($newDataUpdate['old_data']) && (isset($newDataUpdate['rfq_new']) && $newDataUpdate['rfq_new'] !== '1')){
                       $oldData = unserialize(base64_decode($newDataUpdate['old_data']));
                       unset($newDataUpdate['old_data']);
                       $oldData = $commHelper->sanitizeData($oldData);
                    }
                    $newData = $commHelper->sanitizeData($newDataUpdate);
                    $crqu = $this->customerconnectMessageRequestCrqu;
                    $duplicate = isset($newData['is_duplicate']) ? true : false;

                    if ($crqu->isActive() && $helper->getMessageType('CRQU')) {

                        if ($customer->isSalesRep()) {
                            $prpHelper = $this->salesRepPricingRuleProductHelper;
                            $failedProducts = $prpHelper->validateLinesForDiscountedPrices($newData['lines']);
                        } else {
                            $failedProducts = array();
                        }

                        if (count($failedProducts) == 0) {

                            $aFiles = array();
                            $lFiles = array();

                            if (isset($newData['attachments'])) {
                                $aFiles = $fileHelper->processPageFiles('attachments', $newData, $duplicate, false);
                            }

                            if (isset($newData['lineattachments'])) {
                                $lFiles = $fileHelper->processPageFiles('lineattachments', $newData, $duplicate, false);
                            }

                            if ($this->registry->registry('download_erp_files')) {
                                $this->messageManager->addSuccessMessage(__('New RFQ request sent. There will be a delay while attachments are synced'));
                                $connection = new \Zend_Http_Client();
                                $adapter = new \Zend_Http_Client_Adapter_Curl();

                                try {
                                    //M1 > M2 Translation Begin (Rule p2-4)
                                    //$connection->setUri(Mage::getUrl('epicor_comm/message/crqu', array('_store' => $this->storeManager->getStore()->getId())));
                                    $connection->setUri($this->_url->getUrl('epicor_comm/message/crqu', array('_store' => $this->storeManager->getStore()->getId())));
                                    //M1 > M2 Translation End

                                    $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 0);
                                    $adapter->setCurlOption(CURLOPT_POST, 1);

                                    $adapter->setCurlOption(CURLOPT_USERAGENT, 'api');
                                    $adapter->setCurlOption(CURLOPT_TIMEOUT, 1);
                                    $adapter->setCurlOption(CURLOPT_HEADER, 0);
                                    $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, false);
                                    $adapter->setCurlOption(CURLOPT_FORBID_REUSE, true);
                                    $adapter->setCurlOption(CURLOPT_CONNECTTIMEOUT, 1);
                                    $adapter->setCurlOption(CURLOPT_DNS_CACHE_TIMEOUT, 10);
                                    $adapter->setCurlOption(CURLOPT_FRESH_CONNECT, true);

                                    $helper = $this->customerconnectHelper;

                                    $newData['account_number'] = $helper->getErpAccountNumber();

                                    $connection->setParameterPost('data', base64_encode(serialize($newData)));
                                    $connection->setAdapter($adapter);
                                    $connection->request(\Zend_Http_Client::POST);
                                } catch (\Exception $e) {
                                    
                                }
                                //M1 > M2 Translation Begin (Rule p2-4)
                                //$url = Mage::getUrl('*/*/index');
                                $url = $this->_url->getUrl('*/*/index');
                                //M1 > M2 Translation End

                                $this->registry->register('rfq_redirect_url', $url);
                                $response = array(
                                    'redirect' => $url,
                                    'error' => false
                                );
                                session_write_close();
                                $this->getResponse()->setBody(
                                    $this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Redirector')->toHtml()
                                );

                            } else {
                                $files = array_merge($aFiles, $lFiles);

                                $crqu->setAction('A');
                                $crqu->setQuoteNumber('');
                                $crqu->setQuoteSequence('');
                                $crqu->setOldData(array());
                                $crqu->setNewData($newData);

                                if ($crqu->sendMessage()) {
                                    $this->messageManager->addSuccessMessage(__('New RFQ request sent successfully'));

                                    $accessHelper = $this->commonAccessHelper;

                                    $access = $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'update', '', 'Access');
                                    $this->registry->register('rfqs_editable', $access);

                                    $rfq = $crqu->getResults();
                                    
                                    $helper->processCrquFilesSuccess($files, $rfq);
                                    $this->registry->register('customer_connect_rfq_details', $rfq);
                                } else {
                                    $helper->processCrquFilesFail($files);
                                    $error = __('RFQ add request failed');
                                }
                            }
                            //M1 > M2 Translation Begin (Rule 55)
                            /* } elseif (count($failedProducts) == 1) {
                              $error = $this->__('Product %s has an invalid price', implode(', ', $failedProducts));
                              } else {
                              $error = $this->__('Products %s have an invalid price', implode(', ', $failedProducts));
                              } */
                        } elseif (count($failedProducts) == 1) {
                            $error = __('Product %1 has an invalid price', implode(', ', $failedProducts));
                        } else {
                            $error = __('Products %1 have an invalid price', implode(', ', $failedProducts));
                        }
                        //M1 > M2 Translation End
                    } else {
                        $error = __('RFQ add not available');
                    }
                } else {
                    $error = __('No Data Sent');
                }
            } catch (\Exception $ex) {
                $error = __('An error occurred, please try again:' . $ex->getMessage());
            }
        }
        if ($error) {
            $this->registry->register('rfq_error', $error);
            //$this->messageManager->addErrorMessage(__($error));
            session_write_close();
            $this->getResponse()->setBody(
                    $this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Showerror')->toHtml()
                                );
        } else {

            $helper = $this->customerconnectHelper;
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
            $url = $this->_url->getUrl('*/*/details', array('quote' => $requested));
            //M1 > M2 Translation End
            $this->registry->register('rfq_redirect_url', $url);
            $response = array(
                'redirect' => $url,
                'error' => false
            );
            $this->getResponse()->setBody(
                    $this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Redirector')->toHtml()
                                );
            session_write_close();

        }
    }
    
    public function getProcessedRfqData($_post)
    {
        $nwData = explode('&', $_post);
        $newData = array();
        foreach ($nwData as $data) {
            parse_str($data, $new);
            $key = key($new);
            switch (true) {
                case $key == 'lines':
                case $key == 'lineattachments':
                    if (isset($new[$key]['new'])) {
                        $lineKey = key($new[$key]['new']);
                        $lineValueKey = key($new[$key]['new'][$lineKey]);
                        $newData[$key]['new'][$lineKey][$lineValueKey] = $new[$key]['new'][$lineKey][$lineValueKey];
                    } else if (isset($new[$key]['existing'])) {
                        $lineKey = key($new[$key]['existing']);
                        $lineValueKey = key($new[$key]['existing'][$lineKey]);
                        $newData[$key]['existing'][$lineKey][$lineValueKey] = $new[$key]['existing'][$lineKey][$lineValueKey];
                    }
                    break;
                case $key == 'old_data':
                    $newData = array_merge($newData, $new);
                    break;
                default:
                    $newData = array_merge_recursive($newData, $new);
                    break;
            }
        }
        return $newData;
    }          

}
