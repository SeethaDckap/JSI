<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;

class Nonerpproductoptions implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'request', 'label' => 'Request'),
            array('value' => 'proxy','label' => 'Proxy'),
   //not yet required         array('value' => 'erpcreate','label' => 'ERP Create'),
        );
    }

}
