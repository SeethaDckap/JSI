<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Helper;


class Data extends \Epicor\Common\Helper\Data
{

    /**
     * @var \Epicor\Reports\Model\Chart\Factory
     */
    protected $reportsChartFactory;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\Reports\Helper\ChartReader $chart
    ) {
        $this->reportsChartFactory = $chart;
        parent::__construct($context);
    }
    
    /**
     * Returns the speed ranges for the speed chart in messaging configuration
     * @param integer|string|\Magento\Store\Model\Store $store
     * @return array
     */
    public function getSpeedRanges($store = null)
    {
        return explode(',', $this->scopeConfig->getValue("epicor_reports_options/speed/time_range", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store));
    }

    /**
     * Returns the configured Resolutions for the Min, Max & Average Chart
     * @return array
     */
    public function getMinMaxAvgResolutionUnits()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$array = (array) Mage::getConfig()->getNode('chart_types/minmaxavg/resolution_units');
        $array = (array)  $this->scopeConfig->getValue('chart_types/minmaxavg/resolution_units');
        //M1 > M2 Translation End

        $arrayConverted = array();
        foreach ($array as $item) {
            $item = (array) $item;
            $arrayConverted[$item['factor']] = $item['label'];
        }
        return $arrayConverted;
    }

    /**
     * Returns the configured Resolutions for the Min, Max & Average Chart
     * @return array
     */
    public function getMinMaxAvgResolutionUnitDefault()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        // $array = (array) Mage::getConfig()->getNode('chart_types/minmaxavg/resolution_units');
        $array = (array)  $this->scopeConfig->getValue('chart_types/minmaxavg/resolution_units');
        //M1 > M2 Translation End


        $this->loadLinq();
        $default = from('$row')->in($array)->where('$row => $row->default == 1')->firstOrDefault();
        return !is_null($default) && isset($default->factor) ? $default->factor : '';
    }

    /**
     * Returns the configured chart types
     * @return array
     */
    public function getChartTypes()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        // $chartTypes = Mage::getConfig()->getNode('chart_types')->asArray();
        $chartTypes = $this->scopeConfig->getValue('chart_types');

        //M1 > M2 Translation End

        $chartTypesConverted = array();
        /* @var $chartType Mage_Core_Model_Config_Element */
        foreach ($chartTypes as $chartTypeKey => $chartType) {
            if ($this->isChartEnabled($chartTypeKey)) {
                $chartTypesConverted[$chartTypeKey] = $chartType['title'];
            }
        }
        return $chartTypesConverted;
    }

    /**
     * Returns the configured message statud
     * @return array
     */
    public function getMessageStatus()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //return Mage::getConfig()->getNode('chart_message_status')->asArray();
        return $this->scopeConfig->getValue('chart_message_status');
        //M1 > M2 Translation End

    }

    /**
     * Returns true|false if the chart is enabled in the configuration, the chartType param must defined in system.xml
     * @param $chartType string
     * @param $store int
     * @return bool
     */
    public function isChartEnabled($chartType, $store = null)
    {
        return $this->scopeConfig->getValue("epicor_reports_options/{$chartType}/enabled", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Loads the linq library
     */
    public function loadLinq()
    {
        $inc = get_include_path();
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //set_include_path($inc . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Linq' . DS);
        //set_include_path($inc . PATH_SEPARATOR . $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LIB_WEB) . DIRECTORY_SEPARATOR . 'Linq' . DIRECTORY_SEPARATOR);
         set_include_path($inc . PATH_SEPARATOR . BP . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Linq' . DIRECTORY_SEPARATOR);
        //M1 > M2 Translation End
        require_once('PHPLinq' . DIRECTORY_SEPARATOR . 'LinqToObjects.php');
    }

    /*
     * Returns the type of cached messages
     */

    public function getCachedValues()
    {
        return array(
            array('value' => 'none', 'label' => __('None')),
            array('value' => 'partial', 'label' => __('Partial')),
            array('value' => 'fully', 'label' => __('Fully'))
        );
    }

    /**
     * Returns the message types configured in EpicorComm
     * @return array
     */
    public function getMessageTypes()
    {
        /* @var $helper_comm_messaging Epicor_Comm_Helper_Messaging */
        $helper_comm_messaging = $this->commMessagingHelper;
        $messageTypes = array_change_key_case($helper_comm_messaging->getMessageTypes(), CASE_UPPER);
        ksort($messageTypes);
        $_messageTypes = array();
        foreach ($messageTypes as $messageTypeCode => $messageTypeDesc) {
            $_messageTypes[] = array('value' => $messageTypeCode, 'label' => $messageTypeDesc);
        }

        return $_messageTypes;
    }

    /**
     * Returns the data from the DB
     * @param $options
     * @return array
     */
    function chartResults($options)
    {
        $model = $this->reportsChartFactory->getChart($options['chart_type']);
        return $model->getResults($options);
    }

}
