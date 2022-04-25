<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details\Transactions;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('dealerconnect_inventory_transactions');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->getGridDetails());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('dealerconnect');
        $this->setMessageType('deid');
        $this->setIdColumn('name');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $inventoryDetails = $this->registry->registry('deid_order_details');
        if($inventoryDetails) {
            /* @var $order \Epicor\Common\Model\Xmlvarien */
            $inventory = ($inventoryDetails->getTransactions()) ? $inventoryDetails->getTransactions()->getasarrayTransaction() : array();

            $this->setCustomData((array)$inventory);
        }
    }


    
    
    public  function getGridDetails()
    {
        $getConfig= $this->getConfig('dealerconnect_enabled_messages/DEID_request/grid_transactionconfig');
        $oldData = unserialize($getConfig);    
        $indexVals = array();
        foreach ($oldData as $key=> $oldValues) {
            $indexVals[$oldValues['index']]['index'] =$oldValues['index'];
            if($oldValues['index'] =="address") {
                $indexVals[$oldValues['index']]['renderer'] = 'Epicor\Dealerconnect\Block\Portal\Inventory\Details\Transactions\Renderer\Address'; 
            }
            $indexVals[$oldValues['index']]['header'] = __(isset($oldValues['header']) ? $oldValues['header'] : '');
            $indexVals[$oldValues['index']]['filter'] = false;
            $indexVals[$oldValues['index']]['sortable'] = false;
            if(strpos($oldValues['index'], "date") != false) {
            	$indexVals[$oldValues['index']]['type'] = 'date';                
            } else {
             	$indexVals[$oldValues['index']]['type'] = 'text';     
            }
        }
        return $indexVals;             
    }    
    

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }      

    public function getRowUrl($row)
    {
        return null;
    }

}