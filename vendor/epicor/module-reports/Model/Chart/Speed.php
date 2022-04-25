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
class Speed extends \Magento\Framework\Model\AbstractModel
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

        $helper = $this->reportsHelper;
        /* @var $helper Epicor_Reports_Helper_Data */

        $table_name = $resource->getTableName('ecc_reports_raw_data');
        $query = "SELECT @SELECT@ FROM {$table_name} WHERE @WHERE@ GROUP BY	@GROUP@;";
        $select = array();
        $speed_ranges = $helper->getSpeedRanges();
        $total_ranges = sizeof($speed_ranges);

        foreach ($speed_ranges as $i => $speed_range) {
            $next_item = $i + 1;
            $select[] = $next_item < $total_ranges ? "SUM(IF(duration >= {$speed_range} and duration < {$speed_ranges[$next_item]}, 1, 0)) as '{$speed_range}-{$speed_ranges[$next_item]}'" : "SUM(IF(duration >= {$speed_range}, 1, 0)) as '{$speed_range} +'";
        }

        $select[] = "UPPER(message_type) AS message_type";
        #$select[] = "message_status";
        $group[] = "UPPER(message_type)";
        #$group[] = "message_status";
        #$where[] = "time >= '".date('Y-m-d', strtotime($options['from']))."'";
        #$where[] = "time < '".date('Y-m-d', strtotime($options['to'].' +1day'))."'";

        $where[] = "time >= '" . $helper->getFormattedInputDate($options['from'], 'YYYY-MM-dd HH:mm:ss', 'datetime') . "'";
        $where[] = "time <= '" . $helper->getFormattedInputDate($options['to'], 'YYYY-MM-dd HH:mm:ss', 'datetime') . "'";

        if ($options['message_status'] != 'combined') {
            if ($options['message_status'] != 'both')
                $where[] = "message_status = '{$options['message_status']}'";
            $group[] = "message_status";
            $select[] = "message_status";
        }
        else {
            $select[] = "'" . __('combined') . "' as message_status";
        }

        $where[] = "message_type IN ('" . implode("', '", $options['message_type']) . "')";

        if ($options['store_id'] != 0) {
            $where[] = "store = '{$options['store_id']}'";
        }

        if (isset($options['cached']) && !empty($options['cached'])) {
            $where[] = "cached IN ('" . implode("', '", $options['cached']) . "')";
        }

        $query = str_replace('@SELECT@', implode(' , ', $select), $query);
        $query = str_replace('@WHERE@', implode(' AND ', $where), $query);
        $query = str_replace('@GROUP@', implode(',  ', $group), $query);


        $readConnection = $resource->getConnection('core_read');
        return $readConnection->fetchAll($query);
    }

}
