<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CustomerController
 *
 * @author David.Wylie
 */
//include_once("Mage/Adminhtml/controllers/CustomerController.php");

abstract class Customer
{

    protected function _isAllowed()
    {
        return true;
    }
}
