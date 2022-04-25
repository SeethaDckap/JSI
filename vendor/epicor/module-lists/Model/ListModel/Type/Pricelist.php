<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Type;


/**
 * Type Class for Price Lists
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Pricelist extends \Epicor\Lists\Model\ListModel\Type\AbstractModel
{

    protected $visibleSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'customers',
        'products',
        'pricing'
    );
    protected $editableSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'customers',
        'products',
        'pricing'
    );

}
