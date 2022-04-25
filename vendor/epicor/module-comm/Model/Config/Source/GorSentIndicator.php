<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class GorSentIndicator
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'order_not_sent', 'label' => 'Order Not Sent'),
            array('value' => 'never_send', 'label' => 'Never Send'),
        );
    }

}
