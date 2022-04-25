<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Type;


/**
 * Type Class for Contracts
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Contract extends \Epicor\Lists\Model\ListModel\Type\AbstractModel
{

    protected $isContract = true;
    protected $isList = false;
    protected $hasErpMsg = true;
    protected $hasExtraFields = true;
    protected $hasExtraProductFields = true;
    protected $supportsAddresses = true;
    protected $erpMsg = 'CCCN';
    protected $erpMsgSections = array(
        'title' => 'Title',
        'contract_status' => 'Contract Status',
//        'settings' => 'Settings',
//        'erpaccount' => 'ERP Account',
//        'default_currency' => 'Default Currency',
//        'sales_rep' => 'Sales Rep',
//        'contact' => 'Contact Name',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'purchase_order_number' => 'Purchase Order Number',
//        'addresses' => 'Addresses',
//        'products' => 'Products',
        'stores' => 'Stores',
        'description' => 'Description'
    );
    protected $supportedSettings = array();
    protected $visibleSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'products',
        'addresses',
        'messagelog',
        'pricing'
    );
    protected $editableSections = array(
        'labels',
        'brands',
        'websites',
        'stores',
    );

}
