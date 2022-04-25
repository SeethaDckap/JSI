<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Model\Chart;


/**
 * Created by PhpStorm.
 * User: lguerra
 * Date: 9/18/14
 * Time: 3:17 PM
 */
class Performance extends \Magento\Framework\Model\AbstractModel
{

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

        /* @var $helper Epicor_Reports_Helper_Data */
        $helper = $this->reportsHelper;
//SELECT 
//	@rownum :=  @rownum + 1 ,
//    duration,
//    UPPER(message_type) AS message_type,
//    message_status
//FROM
//    ecc_reports_raw_data
//	cross join ( select @rownum := 0) r
//WHERE
//    time >= '2014-10-07'
//        AND time < '2014-10-23'
//        AND message_type IN ('BSV' , 'GOR', 'SOU')
//ORDER BY duration DESC
        $table_name = $resource->getTableName('ecc_reports_raw_data');
        $query = "SELECT @SELECT@ FROM {$table_name} cross join ( select @rownum := 0) r WHERE @WHERE@ ORDER BY duration DESC;";
        $select = array();
        $speed_ranges = $helper->getSpeedRanges();
        $total_ranges = sizeof($speed_ranges);



        $select[] = "@rownum :=  @rownum + 1 as message_id";
        $select[] = "duration";
        $select[] = "UPPER(message_type) AS message_type";
        #$where[] = "time >= '".date('Y-m-d', strtotime($options['from']))."'";
        #$where[] = "time < '".date('Y-m-d', strtotime($options['to'].' +1day'))."'";

        $where[] = "time >= '" . $helper->getFormattedInputDate($options['from'], 'YYYY-MM-dd HH:mm:ss', 'datetime') . "'";
        $where[] = "time <= '" . $helper->getFormattedInputDate($options['to'], 'YYYY-MM-dd HH:mm:ss', 'datetime') . "'";

        $where[] = "message_type IN ('" . implode("', '", $options['message_type']) . "')";
        if ($options['message_status'] != 'combined') {
            if ($options['message_status'] != 'both')
                $where[] = "message_status = '{$options['message_status']}'";
            $select[] = "message_status";
        }
        else {
            $select[] = "'" . __('combined') . "' as message_status";
        }
        if ($options['store_id'] != 0) {
            $where[] = "store = '{$options['store_id']}'";
        }

        if (isset($options['cached']) && !empty($options['cached'])) {
            $where[] = "cached IN ('" . implode("', '", $options['cached']) . "')";
        }

        $query = str_replace('@SELECT@', implode(' , ', $select), $query);
        $query = str_replace('@WHERE@', implode(' AND ', $where), $query);

        $readConnection = $resource->getConnection('core_read');
        $results = $readConnection->fetchAll($query);

        return $this->groupResults($results);
    }

    function groupResults($results)
    {
        $groupedResults = array();
        foreach ($results as $result) {
            $key = $result['message_type'] . '_' . $result['message_status'];
            if (!isset($groupedResults[$key])) {
                $groupedResults[$key] = array(
                    'message_type' => $result['message_type'],
                    'message_status' => $result['message_status'],
                );
            }

            $groupedResults[$key][] = $result['duration'];
        }

        return array_values($groupedResults);
    }

}
