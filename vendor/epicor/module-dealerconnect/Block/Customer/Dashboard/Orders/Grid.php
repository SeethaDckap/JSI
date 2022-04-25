<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard\Orders;

/**
 * Customer Orders list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Customer\Dashboard\Grid
{

    const FRONTEND_RESOURCE_DETAIL_DEALER = "Dealer_Connect::dealer_orders_details";

    const FRONTEND_RESOURCE_DETAIL = "Epicor_Customerconnect::customerconnect_account_orders_details";

    const FRONTEND_RESOURCE_REORDER = "Dealer_Connect::dealer_orders_reorder";

    const FRONTEND_RESOURCE_PRINT = "Dealer_Connect::dealer_orders_print";

    const FRONTEND_RESOURCE_EMAIL = "Dealer_Connect::dealer_orders_email";

    protected $dashboardSection = 'dealer_dashboard_orders';

    protected $_defaultSort = 'order_number';

    protected $id = 'dealerconnect_recent_orders';

    protected $messageBase = 'customerconnect';

    protected $messageType = 'cuos';

    protected $idColumn = 'order_number';

    protected $entityType = 'Order';

    protected $massAction = true;

    protected $_defaultDateFilter = 'order_date';

    protected $_statusFilter = [
        'status'=> 'order_status',
        'value' => 'O'
    ];

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $order_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getId()));
            $backUrl = $this->getUrl('dealerconnect/dashboard/index');
            $pos = strpos($backUrl, "?");
            if ($pos !== false){
                $backUrl = substr($backUrl, 0, $pos);
            }
            $back = $this->urlEncoder->encode($backUrl);
            if (is_null($row->getData('dealer'))) {
                if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL)){
                    $url = $this->getUrl('customerconnect/orders/details', array('order' => $order_requested, 'back' => $back));
                }
            } else {
                if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL_DEALER)){
                    $url = $this->getUrl('dealerconnect/orders/details', array('order' => $order_requested, 'back' => $back));
                }else{
                    $url = $this->getUrl('customerconnect/orders/details', array('order' => $order_requested, 'back' => $back));
                }
            }
        }
        return $url;
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        if ($this->listsFrontendContractHelper->contractsDisabled()) {
            unset($columns['contracts_contract_code']);
        }

        if ($this->isReorderAllowed() || $this->isPrintEmailAllowed()) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\Reorder',
                );
            }
        }

        $columns['dealer_grand_total_inc']['column_css_class'] = 'no-display';
        $columns['dealer_grand_total_inc']['header_css_class'] = 'no-display';
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);
        $this->eventManager->dispatch('epicor_customerconnect_cuos_grid_columns_after', array(
                'block' => $this,
                'columns' => $columnObject
            )
        );

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2,3])){
            $columnsToHide =  ['original_value', 'dealer_grand_total_inc'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2])){
            $columnsToHide =  ['reorder'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        $this->setCustomColumns($columnObject->getData());
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/orders');
    }

    public function setCollection($collection)
    {
        $dashboardConfiguration = $this->getDashboardConfiguration();
        $dashboardConfiguration = array_filter($dashboardConfiguration, function($data){
            return $data['allowed'];
        });
        if (isset($dashboardConfiguration[$this->dashboardSection]['filters'])) {
            $collection->setRowFilters($dashboardConfiguration[$this->dashboardSection]['filters']);
        }
        $this->_collection = $collection;
    }

    public function isRowUrlAllowed()
    {
        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL) || $this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL_DEALER)) {
            return true;
        }
        return false;
    }
}
