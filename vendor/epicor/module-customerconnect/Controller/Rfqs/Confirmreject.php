<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

use Epicor\Comm\Helper\Configurator;
use Epicor\Comm\Helper\Data;
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
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Confirmreject extends Rfqs
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
     * @var Data
     */
    protected $commHelper;

    /**
     * @var Crqc
     */
    protected $customerconnectMessageRequestCrqc;

    /**
     * Confirmreject constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ResolverInterface $localeResolver
     * @param PageFactory $resultPageFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param Registry $registry
     * @param CustomerconnectHelper $customerconnectHelper
     * @param Http $request
     * @param Crqd $customerconnectMessageRequestCrqd
     * @param Generic $generic
     * @param Access $commonAccessHelper
     * @param Messaging $customerconnectMessagingHelper
     * @param CommMessagingHelper $commMessagingHelper
     * @param Configurator $commConfiguratorHelper
     * @param Product $commProductHelper
     * @param ProductFactory $catalogProductFactory
     * @param StoreManagerInterface $storeManager
     * @param CdmFactory $commMessageRequestCdmFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param XmlvarienFactory $commonXmlvarienFactory
     * @param DecoderInterface $urlDecoder
     * @param EncryptorInterface $encryptor
     * @param Data $commHelper
     * @param Crqc $customerconnectMessageRequestCrqc
     */
    public function __construct (
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
        Data $commHelper,
        Crqc $customerconnectMessageRequestCrqc
    ) {
        $this->commHelper = $commHelper;
        $this->customerconnectMessageRequestCrqc = $customerconnectMessageRequestCrqc;
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
        $data = $this->getRequest()->getPost();

        if ($data) {
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
                }
            } else {
                $this->messageManager->addErrorMessage(__('RFQ updating not available'));
            }

            if ($this->messageManager->getMessages()->getItems()) {
                $this->_redirect('*/*/index');
            }
        }
    }

}
