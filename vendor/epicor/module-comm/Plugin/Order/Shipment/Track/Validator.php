<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Order\Shipment\Track;

use Magento\Sales\Model\Order\Shipment\Track;

/**
 * Class Validator
 */
class Validator
{
        
    public function afterValidate(
        \Magento\Sales\Model\Order\Shipment\Track\Validator $subject,
        array $result
    ) {
        if(isset($result['track_number'])){
            unset($result['track_number']);
        }
        return $result;
    }    
}
