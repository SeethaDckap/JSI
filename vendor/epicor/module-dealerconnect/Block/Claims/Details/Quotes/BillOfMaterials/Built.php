<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials;


class Built extends \Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Built
{

    protected function _setupGrid()
    {
        $this->_controller = 'claims_details_quotes_billOfMaterials_built';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('As Built');
    }
}
