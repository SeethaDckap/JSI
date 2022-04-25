<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Config\Source;


/**
 * CUAD months dropdown
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class MiscViewType
{

    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => 'Contracted'),
            array('value' => 1, 'label' => 'Expanded'),
        );
    }

}
