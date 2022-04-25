<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Update extends \Epicor\Customerconnect\Controller\Rfqs
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

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
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
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu,
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper
    )
    {
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->commonFileHelper = $commonFileHelper;
        $this->commHelper = $commHelper;
        $this->customerconnectMessageRequestCrqu = $customerconnectMessageRequestCrqu;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $registry,
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCrqd,
            $generic,
            $commonAccessHelper,
            $customerconnectMessagingHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor
        );
    }

    public function execute()
    {
        $files = array();

        $error = '';
        $deliveryAddress = $this->getRequest()->getPost('delivery_address');
        $error = $this->getRequest()->getPost('delivery_address') ? $this->customerconnectHelper->addressValidate($deliveryAddress, true) : null;
        if (!isset($error)) {
            try {
                if ($newData = $this->getRequest()->getPost()) {
                    $_post = $this->getRequest()->getPost();
                    $newDataUpdate = $this->getProcessedRfqData($_post['rfq_serialize_data']);                       
                    
                    $helper = $this->customerconnectRfqHelper;
                    $fileHelper = $this->commonFileHelper;
                    $customer = $this->customerSession->getCustomer();
                    $commHelper = $this->commHelper;
                    $oldData = unserialize(base64_decode($newDataUpdate['old_data']));
                    if($newDataUpdate['old_data'])
                    unset($newDataUpdate['old_data']);
                    $newData = $commHelper->sanitizeData($newDataUpdate);
                    $oldData = $commHelper->sanitizeData($oldData);
                    $crqu = $this->customerconnectMessageRequestCrqu;

                    if ($crqu->isActive() && $helper->getMessageType('CRQU')) {

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

                        if ($customer->isSalesRep()) {
                            $prpHelper = $this->salesRepPricingRuleProductHelper;
                            $failedProducts = $prpHelper->validateLinesForDiscountedPrices($newData['lines']);
                        } else {
                            $failedProducts = array();
                        }

                        if (count($failedProducts) == 0) {
                            if ($crqu->sendMessage()) {
                                $this->registry->register('rfqs_editable', true);

                                $rfq = $crqu->getResults();

                                $helper->processCrquFilesSuccess($files, $rfq);

                                $this->registry->register('customer_connect_rfq_details', $rfq);
                            } else {

                                $helper->processCrquFilesFail($files);
                                $error = __('RFQ update request failed');
                            }
                            //M1 > M2 Translation Begin (Rule 55)
                            /*} elseif (count($failedProducts) == 1) {
                                $error = $this->__('Product %s has an invalid price', implode(', ', $failedProducts));
                            } else {
                                $error = $this->__('Products %s have an invalid price', implode(', ', $failedProducts));
                            }*/
                        } elseif (count($failedProducts) == 1) {
                            $error = __('Product %1 has an invalid price', implode(', ', $failedProducts));
                        } else {
                            $error = __('Products %1 have an invalid price', implode(', ', $failedProducts));
                        }
                        //M1 > M2 Translation End
                    } else {
                        $error = __('RFQ update not available');
                    }
                } else {
                    $error = __('No Data Sent');
                }
            } catch (\Exception $ex) {
                $error = __('An error occurred, please try again');
            }
        }
        if ($error) {
            $this->registry->register('rfq_error', $error);
            session_write_close();
            $this->getResponse()->setBody(
                    $this->_view->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Rfqs\Details\Showerror')->toHtml()
            );
        } else {
            $result = $this->resultPageFactory->create();
            return $result;           
        }
    }
    
    public function getProcessedRfqData($_post)
    {
        $nwData = explode('&', $_post);
        $newData = array();
        foreach ($nwData as $data) {
            parse_str($data, $new);
            $key = key($new);
            if($key =="lines") {
                    if (isset($new[$key]['new'])) {
                        $lineKey = key($new[$key]['new']);
                        $lineValueKey = key($new[$key]['new'][$lineKey]);
                        $newData[$key]['new'][$lineKey][$lineValueKey] = $new[$key]['new'][$lineKey][$lineValueKey];
                    } else if (isset($new[$key]['existing'])) {
                        $lineKey = key($new[$key]['existing']);
                        $lineValueKey = key($new[$key]['existing'][$lineKey]);
                        $newData[$key]['existing'][$lineKey][$lineValueKey] = $new[$key]['existing'][$lineKey][$lineValueKey];
                    }                
            } else if ($key =="lineattachments") {
                    if (isset($new[$key]['new'])) {
                        $lineKey = key($new[$key]['new']);
                        $lineValueKey = key($new[$key]['new'][$lineKey]);
                        $newData[$key]['new'][$lineKey][$lineValueKey] = $new[$key]['new'][$lineKey][$lineValueKey];
                    } else if (isset($new[$key]['existing'])) {
                        $lineKey = key($new[$key]['existing']);
                        $lineValueKey = key($new[$key]['existing'][$lineKey]);
                        $keyvals = $new[$key]['existing'][$lineKey][$lineValueKey];
                        foreach($keyvals as $keyloose => $vals) {
                            $newData[$key]['existing'][$lineKey][$lineValueKey][$keyloose] = $vals;
                        }
                    }                
            } else if ($key =="old_data") {
                    $newData = array_merge($newData, $new);
            } else {
                 $newData = array_merge_recursive($newData, $new);
            }
        }    
        
        return $newData;
    }    

}
