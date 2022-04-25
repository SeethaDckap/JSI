<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Renderer;


/**
 * Column Renderer for Sales ERP Account Select Grid Address
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Reorder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }    

    public function render(\Magento\Framework\DataObject $row)
    {
        if($this->registry->registry('unique_id')){
            $uniqueId = uniqid();
        }
        $html = '';
        $sku = $row['new']['product_code']?:$row['product_code'];
        $uom = $row['unit_of_measure_code'];
        $qty = $row['quantity'];
        $params = array('sku'=>$sku,'qty'=>$qty,'uom'=>$uom,'id'=>$uniqueId);
        $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing\Reorder');
        $html .= $block->setData($params)->toHtml();
        return $html;
    }

}
