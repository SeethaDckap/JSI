<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Plugin;

/**
 * Description of ToOrder
 *
 * @author 
 */
class ToOrderAddress {
 


    /**
     * To add filedset.xml custom values in the order
     * @return array
     */
    public function aroundConvert(\Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
            \Closure $proceed,\Magento\Quote\Model\Quote\Address $object, $additional = [])
    { 
            $result = $proceed($object, $additional);
            $result->setEccErpAddressCode($object->getEccErpAddressCode());
            
            return $result;
    }
    
}
