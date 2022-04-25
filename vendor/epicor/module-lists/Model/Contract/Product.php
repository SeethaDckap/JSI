<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Contract;


/**
 * Model Class for Contract Products
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getContractId()
 * @method string getListProductId()
 * @method string getLineNumber()
 * @method string getPartNumber()
 * @method string getStatus()
 * @method string getStartDate()
 * @method string getEndDate()
 *
 * @method setContractId()
 * @method setListProductId()
 * @method setLineNumber()
 * @method setPartNumber()
 * @method setStatus()
 * @method setStartDate()
 * @method setEndDate()
 */
class Product extends \Epicor\Database\Model\Contract\Product
{


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\Contract\Product');
    }

}
