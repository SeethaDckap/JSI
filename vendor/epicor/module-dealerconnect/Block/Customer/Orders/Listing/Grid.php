<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Orders\Listing;

/**
 * Dealer Orders list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect 
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Customerconnect\Block\Customer\Orders\Listing\Grid
{
    const FRONTEND_RESOURCE_EXORT = "Dealer_Connect::dealer_orders_export";

    const FRONTEND_RESOURCE_DETAIL = "Dealer_Connect::dealer_orders_details";

    const FRONTEND_RESOURCE_REORDER = "Dealer_Connect::dealer_orders_reorder";

    const FRONTEND_RESOURCE_PRINT = "Dealer_Connect::dealer_orders_print";

    const FRONTEND_RESOURCE_EMAIL = "Dealer_Connect::dealer_orders_email";


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
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Dealerconnect\Block\Customer\Dashboard\Orders\Renderer\Reorder',
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
        if ($this->getIsExport()) {
            $dealerData = $columnObject->getData('dealer_grand_total_inc');
            if ($dealerData['header_css_class'] === "no-display") {
                $columnObject->unsetData('dealer_grand_total_inc');
            } else {
                $columnObject->unsetData('original_value');
            }
        }

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

    public function setCollection($collection)
    {
        $dealerFilter = [
            'dealer' => 'Y'
        ];
        $collection->setRowFilters($dealerFilter);
        $this->_collection = $collection;
    }

}
