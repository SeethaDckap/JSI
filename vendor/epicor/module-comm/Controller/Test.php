<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;


/**
 * Data controller
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class Test extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    protected $_debug = false;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\CacheInterface $cacheManager
    ) {
        $this->resourceConfig=$resourceConfig;
        $this->moduleReader=$moduleReader;
        $this->cache = $cacheManager;
        parent::__construct(
            $context
        );
    }

public function uploadXSDValidation($msgType)
    {
        $xml_str = '';
        $this->debug($xml_str);
        $this->_schemeValidation($xml_str, 'upload' . '/' . "{$msgType}.xsd");

    }


    public function _schemeValidation($xml_str, $xsdFilename)
    {
        echo '<pre>';
        //M1 > M2 Translation Begin (Rule P2-5.7)
        //$filepath = (Mage::getModuleDir('base', 'Epicor_Common') . '/' . 'xsd' . '/' . $xsdFilename);
        $filepath = ($this->moduleReader->getModuleDir('base', 'Epicor_Common') . '/' . 'xsd' . '/' . $xsdFilename);
        //M1 > M2 Translation End

        libxml_use_internal_errors(true);
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->loadXML($xml_str);

        echo '<div id="valid">' . var_export($xml->schemaValidate($filepath), true) . '</div>';
        echo '<div id="errors">';
        var_dump(libxml_get_errors());
        libxml_use_internal_errors(false);
        echo '</div>';
    }

    public function debug($xml_str)
    {
        if ($this->_debug) {
            echo '<pre>';
            var_dump(htmlentities($xml_str));
            echo '<hr>';
        }
    }
/**
     * Logs a message for the auto sync
     * 
     * @param string $message
     */
    protected function autoSyncLog($message)
    {
        Mage::log($message, null, 'auto_sync.log');
    }

    public function setConfigDates($startDateAndTime, $nextDateFromInSyn, $nextRunDateAndTime, $freqValue)
    {
        $nextRunDateAndTime = $nextRunDateAndTime ? $nextRunDateAndTime : $startDateAndTime;
        $currentTimestamp = time();
        $currentDateAndTime = strtotime(date(DATE_ATOM, $currentTimestamp));
        $twentyFourHourDateAndTime = strtotime(date("Y-m-d H:i:s", $currentTimestamp + 86400));
        $startTime = date("H:i:s", $startDateAndTime);
        $currentDate = strtotime(date("Y-m-d", $currentTimestamp));
        while ($currentDateAndTime > $nextRunDateAndTime) {
            $nextRunDateAndTime = $nextRunDateAndTime + $freqValue;
        };
        $nextRunDate = date("Y-m-d", $nextRunDateAndTime);
        if ($nextRunDateAndTime < $twentyFourHourDateAndTime) {
            $dateDiff = strtotime($nextRunDate) - $currentDate;
            $difference = $dateDiff / 86400;
            // if == 1, date is tomorrow
            if ($difference == 1) {
                $nextRunDateAndTime = strtotime($nextRunDate . " " . $startTime);
            }
        }

        $this->autoSyncLog('Setting Next run time to : ' . $nextRunDateAndTime);
        $this->autoSyncLog('Setting Next From Date to : ' . $nextDateFromInSyn);
        //M1 > M2 Translation Begin (Rule P2-2)
        //Mage::getConfig()->saveConfig('epicor_comm_enabled_messages/syn_request/autosync_next_rundate', $nextRunDateAndTime, 'default', 0);
        //Mage::getConfig()->saveConfig('epicor_comm_enabled_messages/syn_request/autosync_next_date_from_in_syn', $nextDateFromInSyn, 'default', 0);
        $this->resourceConfig->saveConfig('epicor_comm_enabled_messages/syn_request/autosync_next_rundate', $nextRunDateAndTime, 'default', 0);
        $this->resourceConfig->saveConfig('epicor_comm_enabled_messages/syn_request/autosync_next_date_from_in_syn', $nextDateFromInSyn, 'default', 0);
        //M1 > M2 Translation End

        //M1 > M2 Translation Begin (Rule P2-6.9)
        //Mage::app()->cleanCache(array('CONFIG'));
        $this->cache->clean(array('CONFIG'));
        //M1 > M2 Translation End
    }
}
