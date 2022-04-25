<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard\Quotes;

use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;
/**
 * Customer Quotes list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Customer\Dashboard\Grid
{

    const FRONTEND_RESOURCE_DETAIL_DEALER = "Dealer_Connect::dealer_quotes_details";

    const FRONTEND_RESOURCE_DETAIL = "Epicor_Customerconnect::customerconnect_account_rfqs_details";

    protected $dashboardSection = 'dealer_dashboard_quotes';

    protected $_defaultSort = 'quote_number';

    protected $id = 'dealerconnect_recent_quotes';

    protected $messageBase = 'customerconnect';

    protected $messageType = 'crqs';

    protected $idColumn = 'quote_number';

    protected $entityType = 'Quotes';

    protected $_defaultDateFilter = 'quote_date';

    public function getRowUrl($row)
    {
        $url = null;
        $msgHelper = $this->commMessagingHelper;
        $enabled = $msgHelper->isMessageEnabled('customerconnect', 'crqd');
        if ($this->isRowUrlAllowed() && $enabled) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $quoteDetails = array(
                'erp_account' => $erp_account_number,
                'quote_number' => $row->getQuoteNumber(),
                'quote_sequence' => $row->getQuoteSequence()
            );
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($quoteDetails)));
            $backUrl = $this->getUrl('dealerconnect/dashboard/index');
            $pos = strpos($backUrl, "?");
            if ($pos !== false){
                $backUrl = substr($backUrl, 0, $pos);
            }
            $back = $this->urlEncoder->encode($backUrl);
            if (is_null($row->getData('dealer'))) {
                if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL)) {
                    $url = $this->getUrl('customerconnect/rfqs/details', array('quote' => $requested, 'back' => $back));
                }
            } else {
                if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL_DEALER)) {
                    $url = $this->getUrl('dealerconnect/quotes/details', array('quote' => $requested, 'back' => $back));
                }else{
                    $url = $this->getUrl('customerconnect/rfqs/details', array('quote' => $requested, 'back' => $back));
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

        $columns['dealer_grand_total_inc']['column_css_class'] = 'no-display';
        $columns['dealer_grand_total_inc']['header_css_class'] = 'no-display';
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        $this->eventManager->dispatch('epicor_customerconnect_crqs_grid_columns_after', array(
                'block' => $this,
                'columns' => $columnObject
            )
        );

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2,3])){
            $columnsToHide = ['original_value', 'dealer_grand_total_inc'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }
        $this->setCustomColumns($columnObject->getData());
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/quotes');
    }

    public function setCollection($collection)
    {
        $dashboardConfiguration = $this->getDashboardConfiguration();
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
