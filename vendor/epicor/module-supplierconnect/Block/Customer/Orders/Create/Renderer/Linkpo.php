<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Create\Renderer;


/**
 * Purchase Order link display
 *
 * @author Pradeep.Kumar
 */
class Linkpo extends \Epicor\Common\Block\Renderer\Encodedlinkabstract
{

    protected $_path = 'supplierconnect/orders/details';
    protected $_key = 'order';
    protected $_accountType = 'supplier';
    protected $_addBackUrl = true;
    protected $_customParams = array(
        'list_url' => 'supplierconnect/orders/new',
        'list_type' => 'New PO'
    );
    protected $_permissions = array(
        'module' => 'Epicor_Supplierconnect',
        'controller' => 'Orders',
        'action' => 'details',
        'block' => '',
        'action_type' => 'Access',
    );

}
