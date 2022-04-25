<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Block\Adminhtml;


/**
 *
 * Block for the view in Epicor reports which communicates with the charts model in order to retrieve the data passed to the view
 *
 * @category   Epicor
 * @package    Epicor_Reports
 * @author     Epicor Websales Team
 */
class Reports extends \Magento\Backend\Block\Widget\Form\Container
{
    /* @var $_modelRawData Epicor_Reports_Model_Rawdata */

    var $_modelRawData = null;
    var $_rangeDates = array();

    /**
     * @var \Epicor\Reports\Model\RawdataFactory
     */
    protected $reportsRawdataFactory;

    /**
     * @var \Epicor\Reports\Helper\Data
     */
    protected $reportsHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\Reports\Model\RawdataFactory $reportsRawdataFactory,
        \Epicor\Reports\Helper\Data $reportsHelper,
        array $data = []
    ) {
        $this->reportsRawdataFactory = $reportsRawdataFactory;
        $this->reportsHelper = $reportsHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->_blockGroup = 'Epicor_Reports';
        $this->_controller = 'adminhtml_reports';
        $this->_mode = 'filter';
    }


    function getModelRawData()
    {
        if (is_null($this->_modelRawData)) {
            $this->_modelRawData = $this->reportsRawdataFactory->create();
        }
        return $this->_modelRawData;
    }

    function getRangeDates($options)
    {
        if (empty($this->_rangeDates)) {
            $this->_rangeDates = $this->getDatesByResolution($options['resolution'], $options['resolution_unit'], $options['from'], $options['to']);
        }
        return $this->_rangeDates;
    }

    function _construct()
    {
        parent::_construct();
        $this->_headerText = __('Total Ordered Report');
    }

    function chartResults($options)
    {
        $helper = $this->reportsHelper;
        /* @var $helper Epicor_Reports_Helper_Data */
        return $helper->chartResults($options);
    }

    public function isChartSpeed($chartOptions)
    {
        $model = $this->getModelRawData();
        return $chartOptions['chart_type'] == $model::REPORT_TYPE_SPEED;
    }

    public function isChartMinMaxAverage($chartOptions)
    {
        $model = $this->getModelRawData();
        return $chartOptions['chart_type'] == $model::REPORT_TYPE_MIN_MAX_AVERAGE;
    }

    public function isChartPerformance($chartOptions)
    {
        $model = $this->getModelRawData();
        return $chartOptions['chart_type'] == $model::REPORT_TYPE_PERFORMANCE;
    }

    function xAxisLabels($chartOptions, $results)
    {
        $labels = array();
        if ($this->isChartSpeed($chartOptions)) {
            $labels = array_diff(array_keys($results[0]), array('message_type', 'message_status'));
        } elseif ($this->isChartMinMaxAverage($chartOptions)) {
            foreach ($results as $result) {
                if (!in_array($result['time_message'], $labels)) {
                    $labels[] = $result['time_message'];
                }
            }
        } elseif ($this->isChartPerformance($chartOptions)) {
            $labels = array_diff(array_keys($results[0]), array('message_type', 'message_status'));
        }

        return $labels;
    }

    function flipResults($results, $columns, $options)
    {
        $this->reportsHelper->loadLinq();
        /* $range_dates = $this->getRangeDates($options);
          $tmp_range_dates = $range_dates;
          array_walk($tmp_range_dates, function(&$item){ $item = "('{$item}')"; });
          echo implode(', ', $tmp_range_dates);


          $columns_converted_range = $this->convertDatesToResolution($range_dates, $columns); */


        $results = json_decode(json_encode($results), FALSE);
        $data = array();
        foreach ($columns as $column) {
            $data[$column] = from('$row')->in($results)->where('$row => $row->time_message == "' . $column . '"')->select('new {
				"message_type" => $row->message_type,
				"message_status" => $row->message_status,
				"min_message" => $row->min_message,
				"max_message" => $row->max_message,
				"average_message" => $row->average_message,
				"time_message" => $row->time_message
			}');
        }
        //7/12/14 6:56 a.m.
        //7/17/14 10:16 a.m.
        $new_data = array();
        $stacked = false;
        foreach ($data AS $hour => $results_by_hour) {
            foreach ($results_by_hour as $result_by_hour) {
                if ($stacked) {
                    $result_by_hour->max_message = $result_by_hour->max_message - $result_by_hour->average_message;
                    $result_by_hour->average_message = $result_by_hour->average_message - $result_by_hour->min_message;
                }

                $new_data['min_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status][$hour] = $result_by_hour->min_message;
                $new_data['min_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_type'] = $result_by_hour->message_type;
                $new_data['min_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_status'] = $result_by_hour->message_status;
                $new_data['min_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_statistic'] = 'MIN';

                $new_data['avg_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status][$hour] = $result_by_hour->average_message;
                $new_data['avg_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_type'] = $result_by_hour->message_type;
                $new_data['avg_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_status'] = $result_by_hour->message_status;
                $new_data['avg_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_statistic'] = 'AVG';

                $new_data['max_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status][$hour] = $result_by_hour->max_message;
                $new_data['max_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_type'] = $result_by_hour->message_type;
                $new_data['max_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_status'] = $result_by_hour->message_status;
                $new_data['max_' . $result_by_hour->message_type . '_' . $result_by_hour->message_status]['message_statistic'] = 'MAX';
            }
        }

        return array_values($new_data);
    }

    private function getDatesByResolution($resolution_value, $resolution_unit, $from, $to)
    {
        $time_cursor = $time_start = strtotime($from);
        $time_end = strtotime($to . ' +1day');
        $dates = array();
        $step = $resolution_unit * $resolution_value;
        //Mage::getSingleton('reports/session')->addError('Error');
        while ($time_cursor < $time_end) {
            $dates[$time_cursor] = date('Y-m-d H:i:s', $time_cursor);
            $time_cursor = $time_cursor + $step;
        }
        return $dates;
    }

    protected function convertDatesToResolution($range_dates, $columns)
    {
        $columns_converted = array();
        foreach ($columns as $column) {
            $columns_converted[$column] = $this->getDateGrouped($column, $range_dates);
        }
        return $columns_converted;
    }

    private function getDateGrouped($search_date, $dates)
    {
        $time_search_date = strtotime($search_date);
        $first = true;
        $previous_date = '';
        $previous_time = 0;
        foreach ($dates as $time => $date) {
            if ($first) {
                $previous_date = $date;
                $previous_time = $time;
                $first = false;
            }
            if ($time_search_date < $time && $time_search_date >= $previous_time) {
                return $previous_date;
            }
            $previous_date = $date;
            $previous_time = $time;
        }
        return $date;
    }

    function switchResults($results)
    {
        $new_results = array();
        foreach ($results as $i => $result) {
            foreach ($result as $key => $value) {
                if (in_array($key, array('message_type', 'message_status'))) {
                    $new_results[$i][$key] = $value;
                } else {
                    $new_results[$i][$value] = $key;
                }
            }
        }

        return $new_results;
    }

}
