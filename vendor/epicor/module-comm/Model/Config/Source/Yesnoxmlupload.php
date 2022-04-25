<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Yesnoxmlupload
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => __('Select option')),
            array('value' => '1', 'label' => __('XML File')),
            array('value' => '2', 'label' => __('XML Text')),
        );
    }

}
