<?php
/**
 * Copyright © 2019-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Message;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Log extends \Epicor\Database\Model\ResourceModel\Message\Log
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
        $writeAdapter = $this->getConnection();
        $defaultLogDays = 30;
        $logDays = $this->scopeConfig->getValue('Epicor_Comm/message_logging/log_days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: $defaultLogDays;

        $dateLimit = date('Y-m-d H:i:s', strtotime("-$logDays days"));
        $gorCleardownInterval = ($this->scopeConfig->getValue('Epicor_Comm/message_logging/gor_cleardown_interval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ?: $defaultLogDays;
        $gorDateLimit = date('Y-m-d H:i:s', strtotime("-$gorCleardownInterval days"));

        $msqCleardownInterval = ($this->scopeConfig->getValue('Epicor_Comm/message_logging/msq_cleardown_interval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ?: $defaultLogDays;
        $msqDateLimit = date('Y-m-d H:i:s', strtotime("-$msqCleardownInterval days"));

        $orderCategory = \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_ORDER;
        $bsv = \Epicor\Comm\Model\Message::MESSAGE_TYPE_BSV;
        $gor = \Epicor\Comm\Model\Message::MESSAGE_TYPE_GOR;
        $msq = \Epicor\Comm\Model\Message::MESSAGE_TYPE_MSQ;
        $writeAdapter->delete(
            $this->getTable('ecc_message_log'), array(
            'start_datestamp < (?)' => $dateLimit,
            //'message_category <> (?)' => $orderCategory,
            'message_type <> (?)' => $gor
            )
        );

        //remove GORs outside keep date  
        $writeAdapter->delete(
            $this->getTable('ecc_message_log'), array('start_datestamp < (?)' => $gorDateLimit,
            'message_type = (?)' => $gor
            )
        );

        //remove MSQs outside keep date
        $writeAdapter->delete(
            $this->getTable('ecc_message_log'), array('start_datestamp < (?)' => $msqDateLimit,
                'message_type = (?)' => $msq
            )
        );
    }

}
