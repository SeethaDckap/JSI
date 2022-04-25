<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Log\View;


class Log extends \Magento\Backend\Block\Template
{

    private $log = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * 
     * @return \Epicor\Comm\Model\Message\Log
     */
    public function getLog()
    {
        if (empty($this->log)) {
            $this->log = $this->registry->registry('message_log_data');
        }
        return $this->log;
    }

    public function getFirstXml()
    {
        $log = $this->getLog();
        if ($log->getMessageParent() == 'Upload') {
            return htmlentities(html_entity_decode($log->getXmlIn()));
        } else {
            return htmlentities(html_entity_decode($log->getXmlOut()));
        }
    }

    public function getMessageStatus()
    {
        $log = $this->getLog();
        $data = $log->getMessageStatus();
        $col = $log->getMessageStatuses();
        $output = $col[$data] ?: 'Unknown';
        return $output;
    }

    public function getSecondXml()
    {
        $log = $this->getLog();
        if ($log->getMessageParent() == 'Upload') {
            return htmlentities(html_entity_decode($log->getXmlOut()));
        } else {
            return htmlentities(html_entity_decode($log->getXmlIn()));
        }
    }

    public function getMessageStatusCode()
    {
        $log = $this->getLog();
        $code = $log->getStatusCode();
        return $code;
    }

    public function getDate($date)
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */
        return $helper->getLocalDate($date,\IntlDateFormatter::MEDIUM, true);
    }

}
