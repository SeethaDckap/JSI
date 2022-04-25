<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\MassActions;

use Epicor\Comm\Model\Message\Log;
use Epicor\Customerconnect\Model\Message\Request\Cpnu;
use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Epicor\Customerconnect\Model\Skus\CpnUpdate;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class MassSkuAdd
 * @package Epicor\Customerconnect\Controller\MassActions
 */
class MassSkuAdd extends Action implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CpnUpdate
     */
    private $cpnUpdate;

    /**
     * @var Session
     */
    private $customerSession;

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
     * MassSkuAdd constructor.
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param CpnUpdate $cpnUpdate
     * @param Session $customerSession
     * @param Cpnu $cpnu
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        CpnUpdate $cpnUpdate,
        Session $customerSession,
        Cpnu $cpnu,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->cpnUpdate = $cpnUpdate;
        $this->customerSession = $customerSession;
        $this->cpnu = $cpnu;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        $skus = array_filter($data['productsku'], 'strlen');

        $noSku = array();
        $cpnItems = array();

        foreach ($skus as $key => $sku) {
            try {
                $product = $this->productRepository->get($sku);
                array_push($cpnItems, array(
                    'pid' => $product->getId(),
                    'psku' => $product->getSku(),
                    'customer_group_id' => $this->getCustomerErpAccId(),
                    'sku' => $data['mysku'][$key],
                    'description' => $data['description'][$key]
                ));

            } catch (NoSuchEntityException $e) {
                array_push($noSku, $sku);
                $notExists = implode(',', $noSku);
                $this->messageManager->addNoticeMessage(__($notExists.' doesn\'t exist.'));
            }
        }

        $this->cpnu->setCpnuAction('A');

        $actual = $this->cpnUpdate->add($cpnItems);

        if (!empty($actual)) {
            $message = $this->cpnu->setNewData($actual);

            if ($message->sendMessage()) {
                $this->logger->info(__('Add Successfull on ERP.'));
            } elseif (
                $this->scopeConfig->isSetFlag(CpnuManagement::XML_PATH_CPNU_SUN, ScopeInterface::SCOPE_STORE) &&
                $this->scopeConfig->isSetFlag(CpnuManagement::XML_PATH_CPNU_SEED, ScopeInterface::SCOPE_STORES) &&
                ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_ERROR)
            ) {
                $this->logger->error($message->getStatusDescription());
            } elseif (
                $this->scopeConfig->isSetFlag(CpnuManagement::XML_PATH_CPNU_WUN, ScopeInterface::SCOPE_STORE) &&
                $this->scopeConfig->isSetFlag(CpnuManagement::XML_PATH_CPNU_WUNE, ScopeInterface::SCOPE_STORES) &&
                ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_WARNING)
            ) {
                $this->logger->error($message->getStatusDescription());
            } else {
                $this->logger->error(__('There is some error while processing your request on ERP.'));
            }
        }

        return $this->resultRedirectFactory->create()->setPath('customerconnect/skus/');
    }

    /**
     * @return mixed
     */
    private function getCustomerErpAccId()
    {
        $data = $this->customerSession->getCustomer()->getData();
        return $data['ecc_erpaccount_id'];
    }
}
