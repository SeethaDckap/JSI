<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Model\Config\Source;


class B2bAccountOptions
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'disable_new_erp_acct', 'label' => "Not Permitted"),
            array('value' => 'no_acct', 'label' => "Account Creation Request (With Admin Email)"),
            array('value' => 'guest_acct', 'label' => "Guest Account Creation (With Admin Email)"),
            array('value' => 'erp_acct', 'label' => "ERP Account Creation"),
            array('value' => 'erp_acct_email', 'label' => "ERP Account Creation (With Admin Email)"),
        );
    }

}
