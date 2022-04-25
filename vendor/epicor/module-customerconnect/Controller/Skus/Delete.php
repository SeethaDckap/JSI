<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Skus;

use Epicor\Comm\Helper\Data;
use Epicor\Comm\Model\Message\Log;
use Epicor\Customerconnect\Helper\Skus;
use Epicor\Customerconnect\Model\Erp\Customer\SkusFactory;
use Epicor\Customerconnect\Model\Message\Request\Cpnu;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 * @package Epicor\Customerconnect\Controller\Skus
 */
class Delete extends \Epicor\Customerconnect\Controller\Skus
{
    /**
     * Configuration path for CPNU Error handling Send User Notification
     */
    const XML_PATH_CPNU_SUN = 'customerconnect_enabled_messages/CPNU_request/error_user_notification';

    /**
     * Configuration path for CPNU Error handling Show ERP Error Description
     */
    const XML_PATH_CPNU_SEED = 'customerconnect_enabled_messages/CPNU_request/error_user_notification_erp';

    /**
     * Configuration path for CPNU Warning actions Send User Notification
     */
    const XML_PATH_CPNU_WUN = 'customerconnect_enabled_messages/CPNU_request/warning_user_notification';

    /**
     * Configuration path for CPNU Warning actions Show ERP Error Description
     */
    const XML_PATH_CPNU_WUNE = 'customerconnect_enabled_messages/CPNU_request/warning_user_notification_erp';

    /**
     * @var SkusFactory
     */
    protected $customerconnectErpCustomerSkusFactory;

    /**
     * @var Data
     */
    protected $commHelper;

    /**
     * @var Cpnu
     */
    private $cpnu;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ResolverInterface $localeResolver
     * @param PageFactory $resultPageFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param Skus $customerconnectSkusHelper
     * @param Generic $generic
     * @param SkusFactory $customerconnectErpCustomerSkusFactory
     * @param Data $commHelper
     * @param Cpnu $cpnu
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ResolverInterface $localeResolver,
        PageFactory $resultPageFactory,
        LayoutFactory $resultLayoutFactory,
        Skus $customerconnectSkusHelper,
        Generic $generic,
        SkusFactory $customerconnectErpCustomerSkusFactory,
        Data $commHelper,
        Cpnu $cpnu,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->customerconnectErpCustomerSkusFactory = $customerconnectErpCustomerSkusFactory;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectSkusHelper,
            $generic
        );
        $this->cpnu = $cpnu;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->customerSession->authenticate($this)) {
            return;
        }

        $errorMsg = __('Error trying to retrieve SKU');
        if (!$this->customerSession->authenticate($this)) {
            return;
        }

        try {
            /* @var $sku \Epicor\Comm\Model\Quote */
            $sku = $this->customerconnectErpCustomerSkusFactory->create()->load($this->getRequest()->get('id'));
            $skuData = $sku->getData();

            $messageData[$skuData['entity_id']] = array(
                'sku' => $skuData['sku'],
                'description' => $skuData['description']
            );

            $erpAccountInfo = $this->commHelper->getErpAccountInfo();

            if ($sku->getCustomerGroupId() == $erpAccountInfo->getId()) {
                $this->cpnu->setCpnuAction('R');
                $message = $this->cpnu->setNewData($messageData);
                if ($message->sendMessage()) {
                    $this->logger->info(__('SKU was successfully deleted from ERP.'));
                } elseif (
                    $this->scopeConfig->isSetFlag(self::XML_PATH_CPNU_SUN, ScopeInterface::SCOPE_STORE) &&
                    $this->scopeConfig->isSetFlag(self::XML_PATH_CPNU_SEED, ScopeInterface::SCOPE_STORES) &&
                    ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_ERROR)
                ) {
                    $this->logger->error($message->getStatusDescription());
                } elseif (
                    $this->scopeConfig->isSetFlag(self::XML_PATH_CPNU_WUN, ScopeInterface::SCOPE_STORE) &&
                    $this->scopeConfig->isSetFlag(self::XML_PATH_CPNU_WUNE, ScopeInterface::SCOPE_STORES) &&
                    ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_WARNING)
                ) {
                    $this->logger->error($message->getStatusDescription());
                } else {
                    $this->logger->info(
                        __('There is some error while processing your request on ERP.')
                    );
                }
                try {
                    $sku->delete();
                    $this->messageManager->addSuccessMessage(__('SKU was successfully deleted from ECC.'));
                    $this->_redirect('*/*');
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(__('There is some error while deletion in ECC.'));
                }
            } else {
                $errorMsg .= __(': You do not have permission to delete this SKU');
                throw new \Exception('Invalid customer');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($errorMsg);
        }
        $redirectResult = $this->resultRedirectFactory->create();
        return $redirectResult->setUrl($this->_redirect->getRefererUrl());
    }
}
