<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Product;


/**
 * Model Class for List Product Price
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * 
 * @method string getListProductId()
 * @method string getCurrency()
 * @method string getPrice()
 * 
 * @method string setListProductId()
 * @method string setCurrency()
 * @method string setPrice()
 */
class Price extends \Epicor\Database\Model\Lists\Product\Price
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Product\Price');
    }

    public function getPriceBreaks()
    {
        return unserialize($this->getData('price_breaks')) ?: array();
    }

    public function getValueBreaks()
    {
        return unserialize($this->getData('value_breaks')) ?: array();
    }

    public function setPriceBreaks($breaks)
    {
        $this->setData('price_breaks', serialize($breaks));

        return $this;
    }

    public function setValueBreaks($breaks)
    {
        $this->setData('value_breaks', serialize($breaks));

        return $this;
    }

}
