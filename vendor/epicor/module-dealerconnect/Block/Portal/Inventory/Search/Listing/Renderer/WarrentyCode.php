<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer;


/**
 * 
 *
 * @author
 */
class WarrentyCode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory
     */
    protected $warrantyCollectionFactory;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
         \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory $warrantyCollectionFactory,
        array $data = []
    ) {
         $this->warrantyCollectionFactory = $warrantyCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $collection = $this->warrantyCollectionFactory->create();
        $collection->addFieldToFilter('code', ['eq' => $row->getWarrantyCode()]);
        $data = $collection->getFirstItem();
        
        if(!empty($data) && $data->getDescription()!=""){
            return $data->getDescription();
        }
            
        return ($row->getWarrantyCode()) ? $row->getWarrantyCode() : '';
    }

}