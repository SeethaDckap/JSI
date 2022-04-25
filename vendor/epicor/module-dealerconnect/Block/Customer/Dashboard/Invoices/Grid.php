<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard\Invoices;


/**
 * Customer Invoices list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Customer\Dashboard\Grid
{

    const FRONTEND_RESOURCE_PRINT = "Epicor_Customerconnect::customerconnect_account_invoices_print";
    const FRONTEND_RESOURCE_EMAIL = "Epicor_Customerconnect::customerconnect_account_invoices_email";
    const FRONTEND_RESOURCE_REORDER = "Epicor_Customerconnect::customerconnect_account_invoices_reorder";
    const FRONTEND_RESOURCE_DETAIL  = "Epicor_Customerconnect::customerconnect_account_invoices_details";

    protected $dashboardSection = 'dealer_dashboard_invoices';

    protected $_defaultSort = 'invoice_number';

    protected $id = 'dealerconnect_recent_invoices';

    protected $messageBase = 'customerconnect';

    protected $messageType = 'cuis';

    protected $idColumn = 'invoice_number';

    protected $entityType = 'Invoice';

    protected $massAction = true;

    protected $_defaultDateFilter = 'invoice_date';

    protected $_statusFilter = [
        'status'=> 'invoice_status',
        'value' => 'O'
    ];

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erpAccountNumber = $helper->getErpAccountNumber();
            $invoice = $this->urlEncoder->encode($this->encryptor->encrypt($erpAccountNumber . ']:[' . $row->getId()));
            $backUrl = $this->getUrl('dealerconnect/dashboard/index');
            $pos = strpos($backUrl, "?");
            if ($pos !== false){
                $backUrl = substr($backUrl, 0, $pos);
            }
            $back = $this->urlEncoder->encode($backUrl);
            $params = [
                        'invoice' => $invoice,
                        'attribute_type' => $row->get_attributesType(),
                        'back' => $back
                        ];
            $url = $this->getUrl('customerconnect/invoices/details', $params);
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

        if(isset($columns['central_collection'])){
            $columns['central_collection']['filter'] = false;
            $columns['central_collection']['sortable'] = false;
            $columns['central_collection']['renderer'] = 'Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\CentrallyCollected';
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
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\Reorder',
                );
            }
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2,3])){
            $columnsToHide =  ['outstanding_value', 'original_value'];
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
        return $this->getUrl('*/dashboard/invoices');
    }
}
