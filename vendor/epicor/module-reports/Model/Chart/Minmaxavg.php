<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Model\Chart;


/**
 * Created by PhpStorm.
 * User: lguerra
 * Date: 9/18/14
 * Time: 3:41 PM
 */
class Minmaxavg extends \Magento\Framework\Model\AbstractModel
{

    protected $_date_format;
    protected $_mysql_date_format;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Reports\Helper\Data
     */
    protected $reportsHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Reports\Helper\Data $reportsHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->reportsHelper = $reportsHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    function getResults($options)
    {
        /* @var $resource Mage_Core_Model_Resource */
        $resource = $this->resourceConnection;

        /** @var $connection Varien_Db_Adapter_Pdo_Mysql */
        $connection = $resource->getConnection('core_read');

        $helper = $this->reportsHelper;
        /* @var $helper Epicor_Reports_Helper_Data */

        $table_name = $resource->getTableName('ecc_reports_raw_data');
        $group = array();
        $select = array();
        $where = array();
        $join_where = array();
        /* ==================QUERY TO INSERT DATES==================== */
        $query = "SELECT\n@SELECT@\nFROM\n{$table_name} AS RawData\nRIGHT JOIN\n@JOIN@\nON\n@WHERE@\nGROUP BY\n@GROUP@;";
        $resolution = $options['resolution'] * $options['resolution_unit'];
        $from = $helper->getFormattedInputDate($options['from'], 'YYYY-MM-dd HH:mm:ss', 'datetime');
        $to = $helper->getFormattedInputDate($options['to'], 'YYYY-MM-dd HH:mm:ss', 'datetime');
        $from_time = strtotime($from);
        $to_time = strtotime($to);
        $offset = $from_time - strtotime(date('Y-m-d 00:00:00', $from_time));

        $this->getDateFormats($resolution);

        $select['message_type'] = "Structure.message_type";
        $select['min_message'] = "COALESCE(MIN(duration), 0)";
        $select['max_message'] = "COALESCE(MAX(duration), 0)";
        $select['average_message'] = "COALESCE(ROUND(AVG(duration)), 0)";
        $select['time_message'] = "Structure.time";
        $select['message_status'] = "Structure.message_status";

        $group[] = "Structure.time";
        $group[] = "UPPER(RawData.message_type)";

        /* JOIN WITH TEMP TABLE THAT HAS THE DEFAULT STRUCTURE */
        $join_where[] = "Structure.time = FROM_UNIXTIME(((UNIX_TIMESTAMP(RawData.time)-{$offset}) DIV {$resolution} * {$resolution})+{$offset}, '{$this->_mysql_date_format}')";
        $join_where[] = "Structure.message_type = RawData.message_type";
        if ($options['message_status'] != 'combined') {
            $join_where[] = "Structure.message_status = RawData.message_status";
        }

        /* FILTERS */

        $where[] = "RawData.time >= '" . $from . "'";
        $where[] = "RawData.time <= '" . $to . "'";
        if ($options['message_status'] != 'combined') {
            if ($options['message_status'] != 'both')
                $where[] = "RawData.message_status = '{$options['message_status']}'";
            $group[] = "RawData.message_status";
        }
        $where[] = "RawData.message_type IN ('" . implode("', '", $options['message_type']) . "')";
        if ($options['store_id'] > 0) {
            $where[] = "RawData.store = '{$options['store_id']}'";
        }
        if (isset($options['cached']) && !empty($options['cached'])) {
            $where[] = "RawData.cached IN ('" . implode("', '", $options['cached']) . "')";
        }

        $queryNumberResults = "SELECT COUNT(*) AS results_count FROM {$table_name} AS RawData WHERE " . implode(' AND ', $where);
        $numberOfResults = $connection->fetchAll($queryNumberResults);


        if ($numberOfResults[0]['results_count'] == 0) {
            return array();
        }

        /* GENERATE THE DATES BETWEEN THE FROM AND TO SEPARATING INTO RESOLUTION */
        $dates = $this->generateDates($from_time, $to_time, $resolution);

        $temp_table = 'temp_structure_' . uniqid();
        $query = str_replace('@SELECT@', $this->selectStatement($select), $query);
        $query = str_replace('@WHERE@', implode(' AND ', $join_where) . ' AND ' . implode(' AND ', $where), $query);
        $query = str_replace('@GROUP@', implode(',  ', $group), $query);
        $query = str_replace('@JOIN@', "{$temp_table} AS Structure", $query);

        $resultSetMainQuery = 2;
        $temp_table_query = $this->createTableStructure($temp_table, $dates, $options['message_type'], $options['message_status']);

        $temp_table_query2 = $this->buildTempTableStructure($temp_table, $dates, $options['message_type'], $options['message_status']);

        $multiQuery = $connection->multiQuery($temp_table_query);     
        $multiQuery = $connection->multiQuery($temp_table_query2);
        $multiQuery = $connection->multiQuery($query);
        
        $results = $multiQuery->fetchAll();

        return $results;
    }

    /**
     * Returns the SELECT statement for the main query as of "field AS alias"
     * @param $selectProperties
     * @return string
     */
    private function selectStatement($selectProperties)
    {
        $return_string = '';
        foreach ($selectProperties as $key => $value) {
            if ($return_string != '')
                $return_string .= ", \n";
            $return_string .= $value . ' AS ' . $key;
        }
        return $return_string;
    }

    /**
     * Returns all the dates between the start and end, separated by the amount of the param $stepInSeconds
     * @param $startTime
     * @param $endTime
     * @param $stepInSeconds
     * @return array
     */
    private function generateDates($startTime, $endTime, $stepInSeconds)
    {
        $time = $startTime;
        $dates = array();
        do {
            $dates[$time] = date($this->_date_format, $time);
            $time += $stepInSeconds;
        } while ($time < $endTime);

        return $dates;
    }

    /**
     * Returns the correct date format used to work on dates for the temp table
     * @param $stepInSeconds
     * @return string
     */
    protected function getDateFormats($stepInSeconds)
    {
        $this->_date_format = 'Y-m-d 00:00:00';
        $this->_mysql_date_format = '%Y-%m-%d 00:00:00';
        if ($stepInSeconds < 3600) {
            $this->_date_format = 'Y-m-d H:i:00';
            $this->_mysql_date_format = '%Y-%m-%d %H:%i:00';
        } elseif ($stepInSeconds < 86400) {
            $this->_date_format = 'Y-m-d H:00:00';
            $this->_mysql_date_format = '%Y-%m-%d %H:00:00';
        }
    }

    /**
     * Returns a string query that has the table default structure to avoid the gaps when there's no data available
     * @param $temp_table
     * @param $dates
     * @param $messageTypes
     * @param $messageStatus
     * @return string
     */
    private function createTableStructure($temp_table, $dates, $messageTypes, $messageStatus)
    {
        $temp_table_query = "CREATE TEMPORARY TABLE {$temp_table}(message_type VARCHAR(5), message_status VARCHAR(20), time DATETIME, PRIMARY KEY (message_type, message_status, time)); ";
         return $temp_table_query;
    }
    private function buildTempTableStructure($temp_table, $dates, $messageTypes, $messageStatus)
    {
        $temp_table_query ="";
        $inserts = "";
        $messageStatusArray = $messageStatus == 'both' ? array('successful', 'unsuccessful') : array($messageStatus);

        foreach ($messageTypes as $type) {
            foreach ($messageStatusArray as $status) {
                foreach ($dates as $date) {
                    if ($inserts != '')
                        $inserts .= ', ';
                    $inserts .= "('{$type}', '{$status}', '{$date}')\n";
                
                }
            }
        }

        $temp_table_query .= "INSERT INTO {$temp_table} VALUES \n{$inserts}; ";
        return $temp_table_query;
    }

}
