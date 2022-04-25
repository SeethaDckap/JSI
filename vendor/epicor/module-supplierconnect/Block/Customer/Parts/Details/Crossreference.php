<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Details;


/**
 * Parts crossreference grid setup
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Crossreference extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'customer_parts_details_crossreference';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Cross Reference Part Information');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
