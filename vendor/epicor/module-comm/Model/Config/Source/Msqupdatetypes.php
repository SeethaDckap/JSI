<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/**
 * MSQ update types - source model
 * 
 * used by config option for MSQ update type
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Msqupdatetypes
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'priceonly', 'label' => 'Prices Only'),
            array('value' => 'stockonly', 'label' => 'Stock Only'),
            array('value' => 'pricesstock', 'label' => 'Prices & Stock')
        );
    }

}
