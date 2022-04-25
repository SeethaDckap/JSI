<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Config\Source;


class LoginModeType
{

    public function toOptionArray()
    {

        return array(
            array('value' => 'dealer', 'label' => 'Dealer'),
            array('value' => 'shopper', 'label' => 'End Customer'),
        );
    }

}
