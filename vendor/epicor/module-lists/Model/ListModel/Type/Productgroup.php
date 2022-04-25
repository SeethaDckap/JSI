<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Type;


/**
 * Type Class for Product Groups
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Productgroup extends \Epicor\Lists\Model\ListModel\Type\AbstractModel
{

    protected $hasErpMsg = true;
    protected $erpMsg = 'CUPG';
    protected $erpMsgSections = array(
        'title' => 'Title',
        'settings' => 'Settings',
        'erpaccounts' => 'ERP Accounts',
        'products' => 'Products',
        'stores' => 'Stores',
        'description' => 'Description'
    );
    protected $visibleSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'customers',
        'products',
        'messagelog',
    );
    protected $editableSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'customers',
        'products',
    );

}
