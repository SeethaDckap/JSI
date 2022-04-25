<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

use Epicor\Comm\Helper\Configurator;
use Epicor\Comm\Helper\Data as CommHelper;
use Epicor\Comm\Helper\Messaging as CommMessagingHelper;
use Epicor\Comm\Helper\Product;
use Epicor\Comm\Model\Message\Log;
use Epicor\Comm\Model\Message\Request\CdmFactory;
use Epicor\Common\Helper\Access;
use Epicor\Common\Model\XmlvarienFactory;
use Epicor\Customerconnect\Controller\Rfqs;
use Epicor\Customerconnect\Helper\Data as CustomerconnectHelper;
use Epicor\Customerconnect\Helper\Messaging;
use Epicor\Customerconnect\Model\Message\Request\Crqc;
use Epicor\Customerconnect\Model\Message\Request\Crqd;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RfqConfirmreject
 * @package Epicor\Dealerconnect\Controller\Claims
 */
class RfqConfirmreject extends Rfqs
{
    /**
     * Configuration path for CRQC Error handling Send User Notification
     */
    const XML_PATH_CRQC_SUN = 'customerconnect_enabled_messages/CRQC_request/error_user_notification';

    /**
     * Configuration path for CRQC Error handling Show ERP Error Description
     */
    const XML_PATH_CRQC_SEED = 'customerconnect_enabled_messages/CRQC_request/error_user_notification_erp';

    /**
     * Configuration path for CRQC Warning actions Send User Notification
     */
    const XML_PATH_CRQC_WUN = 'customerconnect_enabled_messages/CRQC_request/warning_user_notification';

    /**
     * Configuration path for CRQC Warning actions Show ERP Error Description
     */
    const XML_PATH_CRQC_WUNE = 'customerconnect_enabled_messages/CRQC_request/warning_user_notification_erp';

    /**
     * @var CommHelper
     */
    protected $commHelper;

    /**
     * @var Crqc
     */
    protected $customerconnectMessageRequestCrqc;

     /**
     * @var Data
     */
    protected $jsonHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        ResolverInterface $localeResolver,
        PageFactory $resultPageFactory,
        LayoutFactory $resultLayoutFactory,
        Registry $registry,
        CustomerconnectHelper $customerconnectHelper,
        Http $request,
        Crqd $customerconnectMessageRequestCrqd,
        Generic $generic,
        Access $commonAccessHelper,
        Messaging $customerconnectMessagingHelper,
        CommMessagingHelper $commMessagingHelper,
        Configurator $commConfiguratorHelper,
        Product $commProductHelper,
        ProductFactory $catalogProductFactory,
        StoreManagerInterface $storeManager,
        CdmFactory $commMessageRequestCdmFactory,
        ScopeConfigInterface $scopeConfig,
        XmlvarienFactory $commonXmlvarienFactory,
        DecoderInterface $urlDecoder,
        EncryptorInterface $encryptor,
        CommHelper $commHelper,
        Crqc $customerconnectMessageRequestCrqc,
        Data $jsonHelper
    ) {
        $this->commHelper = $commHelper;
        $this->customerconnectMessageRequestCrqc = $customerconnectMessageRequestCrqc;
        $this->jsonHelper = $jsonHelper;
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

    /**
     * Confirm / reject new submit action
     */
    public function execute()
    {
        $success = false;
        $data = $this->getRequest()->getPost();

        if ($data) {
            $rfq_data = json_decode($data['rfq'], true);
            $rfq_list= array();
            if(!empty($rfq_data)){
                foreach($rfq_data as $key=>$val){
                    $rfq_list[$val[0]['quote_number']] = $val[0];
                }
                $data['rfq'] = $rfq_list;
            }else{
                $success = false;
                 $this->messageManager->addErrorMessage('No RFQs selected From Claim.');

                return  $this->getResponse()->representJson(
                   $this->jsonHelper->jsonEncode(array('success'=>$success))
               );
            }

            $data = $this->commHelper->sanitizeData($data);
            $message = $this->customerconnectMessageRequestCrqc;
            $messageTypeCheck = $message->getHelper()->getMessageType('CRQC');

            if ($message->isActive() && $messageTypeCheck) {

                if (empty($data['confirmed']) && empty($data['rejected'])) {
                    $this->messageManager->addErrorMessage('No RFQs selected');
                } else {
                    $message->setRfqData($data['rfq']);

                    if (isset($data['confirmed']) && !empty($data['confirmed'])) {
                        $message->setConfirmed($data['confirmed']);
                    }

                    if (isset($data['rejected']) && !empty($data['rejected'])) {
                        $message->setRejected($data['rejected']);
                    }

                    if ($message->sendMessage()) {
                        $this->messageManager->addSuccessMessage(__('RFQs processed successfully'));
                    } elseif (
                        $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_SUN, ScopeInterface::SCOPE_STORE) &&
                        $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_SEED, ScopeInterface::SCOPE_STORES) &&
                        ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_ERROR)
                    ) {
                        $this->messageManager->addErrorMessage(__($message->getStatusDescription()));
                    } elseif (
                        $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_WUN, ScopeInterface::SCOPE_STORE) &&
                        $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_WUNE, ScopeInterface::SCOPE_STORES) &&
                        ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_WARNING)
                    ) {
                        $this->messageManager->addErrorMessage(__($message->getStatusDescription()));
                    } else {
                        $this->messageManager->addErrorMessage(__('Failed to process RFQs '));
                    }
                    $success = true;
                }
            }else {
                $this->messageManager->addErrorMessage( __('RFQ updating not available'));
                 $success = false;
            }
        }else{
             $success = false;
             $this->messageManager->addErrorMessage('No RFQs selected.');
        }

        return  $this->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode(array('success'=>$success))
                    );
    }
}
