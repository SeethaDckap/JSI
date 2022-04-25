<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Model;


/**
 * Dealer Dashboard model
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 *
 */
class Dashboard extends \Epicor\Common\Model\AbstractModel
{

    const ACCOUNT_TYPE = 'dealer';

    protected $gridConfig = [
        'status' => \Epicor\Common\Model\Managedashboard::STATUS,
        'date_range' => \Epicor\Common\Model\Managedashboard::DATE_RANGE,
        'grid_count' => \Epicor\Common\Model\Managedashboard::GRID_COUNT
    ];

    protected $_summarySection = [
        'dealer_dashboard_claimsection',
    ];

    protected $_dashboardSection = [
        'dealer_dashboard_claims' => ['type' => 'claim', 'resource' => 'Dealer_Connect::dealer_claim_read'],
        'dealer_dashboard_quotes' => ['type' => 'quote', 'resource' => 'Dealer_Connect::dealer_quotes_read'],
        'dealer_dashboard_orders' => ['type' => 'order', 'resource' => 'Dealer_Connect::dealer_orders_read'],
        'dealer_dashboard_invoices' => ['type' => 'invoice', 'resource' => 'Epicor_Customerconnect::customerconnect_account_invoices_read'],
        'dealer_dashboard_shipments' => ['type' => 'shipment', 'resource' => 'Epicor_Customerconnect::customerconnect_account_shipments_read'],
        'dealer_dashboard_claimsection' => ['type' => 'claim_section', 'resource' => 'Dealer_Connect::dealer_claim_read'],
    ];

    protected $_dealerGridFilters = [
        'dealer_dashboard_quotes' => [
            'dealer' => 'Y'
        ],
        'dealer_dashboard_orders' => [
            'dealer' => 'Y'
        ],
    ];

    /**
     * @var \Epicor\Common\Model\Managedashboard
     */
    protected $manageDashboard;

    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $_helper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * Dashboard constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Common\Model\Managedashboard $managedashboard
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\Managedashboard $managedashboard,
        \Epicor\Dealerconnect\Helper\Messaging $helper,
        \Epicor\AccessRight\Model\Authorization $_accessauthorization,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->managedashboard = $managedashboard;
        $this->_helper = $helper;
        $this->_accessauthorization = $_accessauthorization;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return array
     */
    public function getDashboardSection()
    {
        return $this->_dashboardSection;
    }

    /**
     * @return array
     */
    public function getDealerGridFilters()
    {
        return $this->_dealerGridFilters;
    }

    /**
     * @return array
     */
    public function getDashboardConfiguration()
    {
        $dashboardConfiguration = $this->managedashboard->getDashboardConfiguration(self::ACCOUNT_TYPE);
        foreach ($this->_dashboardSection as $section => $info) {
            if(isset($dashboardConfiguration[$section])) {
                $_accessAllowed = $this->_accessauthorization->isAllowed($info['resource']);
                if (in_array($section, $this->_summarySection)) {
                    $dashboardConfiguration['summary'][$section] = $dashboardConfiguration[$section];
                    $dashboardConfiguration['summary'][$section]['allowed'] = (!$_accessAllowed) ? 0 : 1;
                    $dashboardConfiguration['summary'][$section]['type'] = $this->_dashboardSection[$section]['type'];
                    $dashboardConfiguration['summary'][$section]['filters'] = json_decode($dashboardConfiguration[$section]['filters'],1);
                    unset($dashboardConfiguration[$section]);
                } else {
                    $dashboardConfiguration[$section]['allowed'] = (!$_accessAllowed) ? 0 : 1;
                    $dashboardConfiguration[$section]['type'] = $this->_dashboardSection[$section]['type'];
                    if (isset($this->_dealerGridFilters[$section])) {
                        $dashboardConfiguration[$section]['filters'] = json_decode($dashboardConfiguration[$section]['filters'], 1);
                    }
                }
            } else {
                $_accessAllowed = $this->_accessauthorization->isAllowed($info['resource']);
                if (in_array($section, $this->_summarySection)) {
                    $dashboardConfiguration['summary'][$section]['status'] = \Epicor\Common\Model\Managedashboard::STATUS;
                    $dashboardConfiguration['summary'][$section]['type'] = $info['type'];
                    $type = str_replace("_section", "", $info['type']);
                    $statuses = $this->_helper->getStatuses($type);
                    $dashboardConfiguration['summary'][$section]['filters']['statuses'] = array_keys($statuses);
                    $dashboardConfiguration['summary'][$section]['allowed'] = (!$_accessAllowed) ? 0 : 1;
                } else {
                    $dashboardConfiguration[$section]['allowed'] = (!$_accessAllowed) ? 0 : 1;
                    foreach ($this->gridConfig as $config => $value) {
                        $dashboardConfiguration[$section][$config] = $value;
                        if (isset($info['type'])) {
                            $dashboardConfiguration[$section]['type'] = $info['type'];
                            if (isset($this->_dealerGridFilters[$section])) {
                                $dashboardConfiguration[$section]['filters'] = $this->_dealerGridFilters[$section];
                            }
                        }
                    }
                }
            }
        }
        if (isset($dashboardConfiguration['summary'])) {
            $_allowed = array_column($dashboardConfiguration['summary'], 'allowed');
            $dashboardConfiguration['summary']['allowed'] = in_array(1, $_allowed) ? 1 : 0;
        }
        return $dashboardConfiguration;
    }

}
