<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Message;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Queue extends \Epicor\Database\Model\ResourceModel\Message\Queue
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $connectionName
        );
    }

 
    public function clean()
    {
        $writeAdapter =  $this->getConnection();
        $timeout = $this->scopeConfig->getValue('Epicor_Comm/message_queue/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: \Epicor\Comm\Model\Message\Upload::DEFAULT_TIMEOUT;
        $timeoutLimit = microtime(true) - ($timeout * 1.5);
        $writeAdapter->delete(
            $this->getTable('ecc_message_queue'), array(
            'created_at < (?)' => $timeoutLimit,
            )
        );
    }

}
