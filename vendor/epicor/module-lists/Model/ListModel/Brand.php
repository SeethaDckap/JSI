<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


/**
 * Model Class for List Brand
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getCompany()
 * @method string getSite()
 * @method string getWarehouse()
 * @method string getGroup()
 *
 * @method string setCompany()
 * @method string setSite()
 * @method string setWarehouse()
 * @method string setGroup()
 */
class Brand extends \Epicor\Database\Model\Lists\Brand
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Brand');
    }

}
