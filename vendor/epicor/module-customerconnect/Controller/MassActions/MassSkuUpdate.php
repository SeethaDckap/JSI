<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\MassActions;

use Epicor\Comm\Model\Message\Log;
use Epicor\Customerconnect\Model\Message\Request\Cpnu;
use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Epicor\Customerconnect\Model\Skus\CpnUpdate;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class MassSkuUpdate
 * @package Epicor\Customerconnect\Controller\MassActions
 */
class MassSkuUpdate extends Action
{
    /**
     * @var Cpnu
     */
    private $cpnu;

    /**
     * @var CpnUpdate
     */
    private $cpnUpdate;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MassSkuUpdate constructor.
     * @param Context $context
     * @param Cpnu $cpnu
     * @param CpnUpdate $cpnUpdate
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Cpnu $cpnu,
        CpnUpdate $cpnUpdate,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cpnu = $cpnu;
        $this->cpnUpdate = $cpnUpdate;
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

        if ($data['entityid']) {
            $ids = explode(',', $data['entityid'][0]);
            $products = array();
            foreach ($ids as $id) {
                $products[$id] = $data['products'][$id];
            }
        } else {
            $products = $data['products'];
        }

        $this->cpnu->setCpnuAction('U');

        $actual = $this->cpnUpdate->update($products);

        if (!empty($actual)) {
            $message = $this->cpnu->setNewData($actual);

            if ($message->sendMessage()) {
                $this->logger->info(__('Update Successfull on ERP.'));
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

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
    }
}
