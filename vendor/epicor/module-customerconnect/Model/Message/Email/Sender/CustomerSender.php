<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\Message\Email\Sender;

use Magento\Framework\Mail\Template\TransportBuilder as MailTransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\Area;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomerSender
{
    /**
     * @var MailTransportBuilder
     */
    private $mailTransportBuilder;

    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CustomerSender constructor.
     * @param MailTransportBuilder $mailTransportBuilder
     * @param ScopeConfig $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        MailTransportBuilder $mailTransportBuilder,
        ScopeConfig $scopeConfig,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->mailTransportBuilder = $mailTransportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Customer $customer
     * @param string $templateEmailConfigPath
     * @param null $webSiteId
     *
     */
    public function send($customer, $templateEmailConfigPath, $storeId = null)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        try {
            $transport = $this->mailTransportBuilder->setTemplateIdentifier(
                $this->scopeConfig->getValue($templateEmailConfigPath, $scope, $storeId)
            )->setTemplateOptions(
                ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                ['customer' => $customer, 'store' => $customer->getStore()]
            )->setFromByScope(
                $this->scopeConfig
                    ->getValue($customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $scope, $storeId),
                $storeId
            )->addTo(
                $customer->getEmail(),
                $customer->getName()
            )->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param $websiteId
     * @return int
     */
    public function getStoreFromWebsite($websiteId)
    {
        $store = $this->getStoreWebsite($websiteId);
        if ($store instanceof \Magento\Store\Model\Store) {
            return $store->getId();
        }
    }

    /**
     * @param $websiteId
     * @return \Magento\Store\Model\Store
     */
    private function getStoreWebsite($websiteId)
    {
        try {
            $website = $this->storeManager->getWebsite($websiteId);
            if ($website instanceof \Magento\Store\Api\Data\WebsiteInterface) {
                return $website->getDefaultStore();
            } else {
                $type = getType($website);
                throw new \InvalidArgumentException(
                    'Instance should be of type \Magento\Store\Api\Data\WebsiteInterface but type ' . $type . ' found'
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}