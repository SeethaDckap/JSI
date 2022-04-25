<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/**
 * @method string getErpCode()
 * @method string getMagentoCode()
 * @method setErpCode(string $erp_code)
 * @method gsetMagentoCode(string $magento_code)
 */
class Country extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country');
    }

}
