<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Location\Product;


/**
 * Location product currency model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetProductId(integer $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetLocationCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetCurrencyCode(float $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetCostPrice(float $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetBasePrice(float $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetCreatedAt(datetime $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency checkAndSetUpdatedAt(datetime $value)
 * 
 * @method \Epicor\Comm\Model\Location\Product\Currency setProductId(integer $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency setLocationCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency setCurrencyCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency setCostPrice(float $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency setBasePrice(float $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency setCreatedAt(datetime $value)
 * @method \Epicor\Comm\Model\Location\Product\Currency setUpdatedAt(datetime $value)
 * 
 * @method integer getProductId()
 * @method string getLocationCode()
 * @method float getCostPrice()
 * @method float getBasePrice()
 * @method datetime getCreatedAt()
 * @method datetime getUpdatedAt()
 */
class Currency extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'ecc_location_product_currency';
    protected $_eventObject = 'location_product_currency';

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Product\Currency');
    }

}
