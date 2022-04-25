<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Helper;


class Data extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */
    protected $commonLocaleFormatDateHelper;

    protected $manageDashboardHelper;

    protected $dashInformation;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Epicor\Supplierconnect\Helper\Dashboard $manageDashboardHelper
    ) {
        $this->commonLocaleFormatDateHelper = $commonLocaleFormatDateHelper;
        $this->manageDashboardHelper = $manageDashboardHelper;
        parent::__construct($context);
    }
    /**
     * Converts a date / timestamp to the format specified, using magento locale dates
     * 
     * @param string $timestamp 
     * @param string $format
     * 
     * @return string
     */
    public function getLocalDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {
        $helper = $this->commonLocaleFormatDateHelper;
        if(strstr($timestamp, "T00:00:00+00:00") > -1){
            $timeStampArray = explode("T00:00:00+00:00",$timestamp);
            $dateOnlyTimestamp = strtotime($timeStampArray[0]);
            $offsetFromGmt = $this->commHelper->UTCwithOffset($dateOnlyTimestamp);
            $behindGmt = strpos($offsetFromGmt, 'T00:00:00-') > -1 ? true : false;

            if($behindGmt){
                //add 1 day to date to counter the day taken off in getLocaleDate for the locale being behind GMT
                $adjustedTimestamp = strtotime("+1 day", $dateOnlyTimestamp);
                $timestamp = $this->getLocalDate($adjustedTimestamp, \IntlDateFormatter::LONG, true);
            }
        }

        return $helper->getLocalDate($timestamp, $format, $showTime);
    }


    public function getDashboardInformation() {
        if(!$this->dashInformation) {
            $this->dashInformation = $this->manageDashboardHelper->getDashboardInformation();
        }
        return $this->dashInformation;
    }

}
