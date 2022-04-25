<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper\Locale\Format;


class Date extends \Epicor\Common\Helper\Locale\Format
{

    /**
     * Converts a date / timestamp to the format specified, using magento locale dates
     * 
     * @param string $timestamp 
     * @param string $format
     * 
     * @return string
     */
    public function getLocalFormatDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {

        //M1 > M2 Translation Begin (Rule p2-6.4)
        /*if ($showTime) {
            $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        } else {
            $format = Mage::app()->getLocale()->getDateFormat($format);
        }*/
        if ($showTime) {
            $format = $this->timezone->getDateTimeFormat($format);
        } else {
            $format = $this->timezone->getDateFormat($format);
        }
        //M1 > M2 Translation End
        $formattedDate = '';

        if (!empty($timestamp)) {
            try {
                $timestamp = !is_numeric($timestamp) ? strtotime($timestamp) : $timestamp;
                $date = new \Zend_Date($timestamp);
                $formattedDate = $date->toString($format);
            } catch (\Exception $ex) {
                $formattedDate = $timestamp;
            }
        }

        return $formattedDate;
    }

}
