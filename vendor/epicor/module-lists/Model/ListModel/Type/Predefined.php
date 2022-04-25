<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Type;


/**
 * Type Class for Recent Purchases
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Predefined extends \Epicor\Lists\Model\ListModel\Type\AbstractModel
{

    protected $visibleSections = array(
        'erpaccounts',
        'customers',
        'products',
    );

}
