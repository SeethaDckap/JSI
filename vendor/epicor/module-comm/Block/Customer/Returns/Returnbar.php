<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Products block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Returnbar extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/customer/returns/return_bar.phtml');
    }

}
