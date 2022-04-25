<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Block
 */
namespace Epicor\Comm\Helper;

use Zend\Log\Writer\Stream;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Logger
 *
 * @package Epicor\Comm\Helper
 */
class CommandLogger extends AbstractHelper
{

    /**
     * Log File Name
     */
    const LOG_FILE = '/var/log/update_category_position';

    /**
     * Logger
     *
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * TimezoneInterface.
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var string
     */
    private $depFileName;


    /**
     * Logger constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate Locale Date.
     */
    public function __construct(
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
        $this->logger     = new \Zend\Log\Logger();

    }//end __construct()


    /**
     * Create log file for CLI command
     */
    public function createCommandLogFile()
    {
        $filename = self::LOG_FILE.'_'.$this->getTimeStamp().'.log';
        $writer   = new Stream(BP.$filename);
        $this->logger->addWriter($writer);
        $this->depFileName = $filename;

    }//end createCommandLogFile()


    /**
     * Get TimeStamp
     *
     * @return mixed
     */
    public function getTimeStamp()
    {
        return $this->localeDate->formatDateTime(
            $this->localeDate->date(),
            null,
            null,
            null,
            null,
            'YMdHHmmss'
        );

    }//end getTimeStamp()


    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->localeDate->formatDateTime(
            $this->localeDate->date(),
            null,
            null,
            null,
            null,
            null
        );

    }//end getTime()


    /**
     * Log message
     *
     * @param string[] $message Message to Log.
     *
     * @return void
     */
    public function log($message)
    {
        $this->logger->info($message);

    }//end log()


    /**
     * Get Deployment Log File
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->depFileName;

    }//end getLogFile()


}//end class
