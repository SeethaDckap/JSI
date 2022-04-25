<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials;


class Additional extends \Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Additional
{

    protected function _setupGrid()
    {
        $this->_controller = 'claims_details_quotes_billOfMaterials_additional';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Additional and Replacements');
    }
}
