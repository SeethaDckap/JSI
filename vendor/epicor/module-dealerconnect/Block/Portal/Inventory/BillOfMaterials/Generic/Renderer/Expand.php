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
class Expand extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Messaging $commMessagingHelper, array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
                $context, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) 
    {
        if($this->registry->registry('debm_info_details')){
            $this->registry->unregister('debm_info_details');
        }
        if($this->registry->registry('unique_id')){
            $this->registry->unregister('unique_id');
        }
        $uniqueId = uniqid();
        $html = "<div class='expand-row'>"
                . "<span class='plus-minus' id='" . $uniqueId . "'>+</span></div>";
        $this->registry->register('debm_info_details', $row);
        $this->registry->register('unique_id', $uniqueId);

        return $html;
    }
    
}
