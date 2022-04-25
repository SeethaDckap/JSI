<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\FindClaimInventoryList;


/**
 * Dealers Claim  Grid config
 * 
 * Note: columns for this grid are not configured in the Magento Admin: Configuration > Dealer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends  \Epicor\Common\Block\Generic\Listing\Grid
{
     /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,    
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );
        
         
        $this->setId('claim_find');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('dealerconnect');
        $this->setMessageType('deis');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

         $claim_search_record = $this->registry->registry('claim_search_record');
         $this->setCustomData($claim_search_record);
        
    }
    

    protected function _getColumns()
    {
        $columns = array();
//
//        $columns['location_number'] = array(
//                "header" => "Claim Number",
//               // "type" => "number",
//                "options" => "",
//                "index" => "location_number",
//                "filter_by" => "linq",
//                "condition" => "LTE/GTE",
//                //"sort_type" => "number",
//                 'filter' => false
//        );
        
        $columns['identification_number'] = array(
                "header" => "identificationNumber",
                "type" => "text",
                "options" => "",
                "index" => "identification_number",
                "filter_by" => "linq",
                "condition" => "EQ",
                "sort_type" => "text",
               // 'filter' => false
        );
        $columns['serial_number'] = array(
                "header" => "Serial Number",
                "type" => "text",
                "options" => "",
                "index" => "serial_number",
                "filter_by" => "linq",
                "condition" => "EQ",
                "sort_type" => "text",
                // 'filter' => false
        );
        
        $columns['product_code'] = array(
                "header" => "Product Code",
                "type" => "text",
                "options" => "",
                "index" => "product_code",
                "filter_by" => "linq",
                "condition" => "EQ",
                "sort_type" => "text",
                // 'filter' => false
        );
        
        $columns['description'] = array(
                "header" => "Description",
                "type" => "text",
                "options" => "",
                "index" => "description",
                "filter_by" => "linq",
                "condition" => "EQ",
                "sort_type" => "text",
                //'filter' => false
        );
        
        $columns['order_number'] = array(
                "header" => "Order Number",
                "type" => "text",
                "options" => "",
                "index" => "order_number",
                "filter_by" => "linq",
                "condition" => "EQ",
                "sort_type" => "text",
               //  'filter' => false
        );
        
        $columns['action'] = array(
            "header" => "Action",
            "type" => "text",
            "filter" => false,
            "sortable" => false,
            "renderer" => "Epicor\Dealerconnect\Block\Claims\FindClaimInventoryList\Renderer\Action",
        );
        
        return $columns;
    }
    
    public function getRowUrl($row)
    {
        return null;
    }
}
