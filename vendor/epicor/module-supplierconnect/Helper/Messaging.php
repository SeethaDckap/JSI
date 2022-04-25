<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Helper;


class Messaging extends \Epicor\Comm\Helper\Messaging
{

    public function getPermissionFor($customer)
    {
        return true;
    }

    public function getErpOrderStatusDescription($code, $description = '')
    {
        $erp = $this->getErpOrderMapping($code);
        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    public function getErpOrderMapping($erpCode)
    {
        $erp = $this->customerconnectErpMappingErporderstatusFactory->create()
            ->load($erpCode, 'code');

        return $erp;
    }

}
