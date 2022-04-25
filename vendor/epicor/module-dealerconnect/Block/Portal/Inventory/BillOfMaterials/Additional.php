<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials;


class Additional extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'portal_inventory_billOfMaterials_additional';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Additional and Replacements');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
