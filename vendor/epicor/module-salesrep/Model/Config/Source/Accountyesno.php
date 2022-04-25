<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Config\Source;


class Accountyesno
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'N', 'label' => 'Global No, but allow Sales Rep Account Level Setting'),
            array('value' => 'Y', 'label' => 'Global Yes, but allow Sales Rep Account Level Setting'),
            array('value' => 'forceY', 'label' => 'Force Yes, for all Sales Rep Accounts'),
            array('value' => 'forceN', 'label' => 'Force No, for all Sales Rep Accounts'),
        );
    }

}
