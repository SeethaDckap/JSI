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
class Serialnumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

    public function render(\Magento\Framework\DataObject $row){
        $html = '';
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);
        if(is_array($value)){
            $value = implode(',', $value);
            $len = strlen($value);
            $substr = substr($value, 0, 40);
            $text = strrpos($substr,",");
            $text = substr($substr, 0, $text);
            $value = $text." <span class='tip'>...<br><span class='tiptext'>".$value."</span></span>";
            $html = '<div class="wrap-info-data">'.$value.'</div>';
            return $html;
        }
        
        return $value;
    }

}
