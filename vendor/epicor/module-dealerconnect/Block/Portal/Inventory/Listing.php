<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Portal\Inventory;

class Listing extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_inventory_read';

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );
        $this->_setupGrid();
        $this->_postSetup();
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/widget/grid/container.phtml');
    }

    protected function _setupGrid()
    {
        $this->_controller = 'portal_inventory_search_listing';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Inventory');

        $url = $this->getUrl('*/*/new');

        if($this->_isAccessAllowed("Dealer_Connect::dealer_inventory_create")){
            $this->addButton(
                'new', array(
                'label' => __('Add Inventory'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'add',
            ), 10
            );
        }

    }

    protected function _postSetup()
    {
        $this->setInventoryTypeCheck(true);
        parent::_postSetup();
    }
}
