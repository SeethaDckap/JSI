<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Listing\Renderer;


/**
 * Invoice link display
 *
 * @author Gareth.James
 */
class Linkinvoice extends \Epicor\Common\Block\Renderer\Encodedlinkabstract
{

    protected $_path = 'customerconnect/invoices/details';
    protected $_key = 'invoice';
    protected $_addBackUrl = true;
    protected $_permissions = "Epicor_Customerconnect::customerconnect_account_invoices_details";

}
