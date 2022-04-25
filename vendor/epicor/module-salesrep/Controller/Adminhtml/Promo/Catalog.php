<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Promo;


/**
 * Account controller
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
//include_once("Mage/Adminhtml/controllers/Promo/CatalogController.php");

abstract class Catalog extends \Magento\Framework\App\Action\Action
{
    function __construct(Context $context)
    {
        parent::__construct($context);
    }

}
