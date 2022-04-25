<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageErrorActions
 *
 * @author David.Wylie
 */
class Erroractions
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'CONTINUE', 'label' => "Continue"),
            array('value' => 'ERROR', 'label' => "Error Page"),
            array('value' => 'OFFLINE-SITE', 'label' => "Offline Site"),
        );
    }

}
