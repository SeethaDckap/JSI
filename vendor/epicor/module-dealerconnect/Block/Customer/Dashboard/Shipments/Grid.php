<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard\Shipments;

/**
 * Customer Orders list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Customer\Dashboard\Grid
{
    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customerconnect::customerconnect_account_shipments_details';

    const FRONTEND_RESOURCE_REORDER = 'Epicor_Customerconnect::customerconnect_account_shipments_reorder';

    const FRONTEND_RESOURCE_PRINT = 'Epicor_Customerconnect::customerconnect_account_shipments_print';

    const FRONTEND_RESOURCE_EMAIL = 'Epicor_Customerconnect::customerconnect_account_shipments_email';

    protected $dashboardSection = 'dealer_dashboard_shipments';

    protected $_defaultSort = 'shipment_date';

    protected $id = 'dealerconnect_recent_shipments';

    protected $messageBase = 'customerconnect';

    protected $messageType = 'cuss';

    protected $idColumn = 'packing_slip';

    protected $entityType = 'Pack';

    protected $massAction = true;

    protected $_defaultDateFilter = 'shipment_date';

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $shipment = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getId() . ']:[' . $row->getOrderNumber()));
            $backUrl = $this->getUrl('dealerconnect/dashboard/index');
            $pos = strpos($backUrl, "?");
            if ($pos !== false){
                $backUrl = substr($backUrl, 0, $pos);
            }
            $back = $this->urlEncoder->encode($backUrl);
            $url = $this->getUrl('customerconnect/shipments/details', array('shipment' => $shipment, 'back' => $back));
        }
        return $url;
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        if ($this->isReorderAllowed() || $this->isPrintEmailAllowed()) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Shipments\Listing\Renderer\Reorder',
                );
            }
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2])) {
            $columnsToHide = ['reorder'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        $this->setCustomColumns($columnObject->getData());
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/shipments');
    }
}
