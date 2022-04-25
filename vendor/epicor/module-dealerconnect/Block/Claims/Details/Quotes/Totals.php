<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes;


/**
 * RFQ line totals display
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Totals extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Totals
{
    public function _construct()
    {
        parent::_construct();
        if ($this->registry->registry('rfqs_editable')) {
            $this->setColumns(11);
        } else {
            $this->setColumns(10);
        }
    }

}
