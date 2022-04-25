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
class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $uniqueId = $this->registry->registry('unique_id');
        $html = '';
        $html .= '
            <div class="dropdown-content">
                <a class="plus-minus" stat="' . $uniqueId . '" >Replace</a>
            </div>';
        return $html;
    }

}
