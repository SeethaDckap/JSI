<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;

/**
 * Description of ImageVisibilty
 *
 * @author ecc
 */
class ImageVisibilty {
    
   public function toOptionArray()
    {
            return array(
                    array('value' => '0', 'label' => "Hidden"),
                    array('value' => '1', 'label' => "Not Hidden"),
            );
    }
}
