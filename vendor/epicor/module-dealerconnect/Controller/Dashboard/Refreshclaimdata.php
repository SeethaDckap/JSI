<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

class Refreshclaimdata extends \Epicor\AccessRight\Controller\Action
{
    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $dealerconnectHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Epicor\Dealerconnect\Model\Claimstatus
     */
    protected $claimstatus;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Epicor\Dealerconnect\Model\Claimstatus $claimstatus
    ) {
        $this->dealerconnectHelper = $dealerconnectHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->claimstatus = $claimstatus;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        $erpAccountNumber = $this->dealerconnectHelper
                            ->getErpAccountInfo()
                            ->getAccountNumber();
        $claimData = $this->claimstatus->processClaims($erpAccountNumber);
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($claimData);
        return $resultJson;
    }

}
