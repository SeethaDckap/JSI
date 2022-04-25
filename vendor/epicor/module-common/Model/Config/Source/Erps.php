<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Source;


class Erps
{
    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;
    public function __construct(
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig
    )
    {
        $this->globalConfig = $globalConfig;
    }

    public function toOptionArray()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$data = (array) Mage::getConfig()->getNode('adminhtml/erps');
        $data = $this->globalConfig->get('adminhtml/erps');
        //M1 > M2 Translation End


        $options = array(array('value' => '', 'label' => '--Please Select--'));
        foreach ($data as $value => $label) {
            $options[] = array('value' => $value, 'label' => __($label));
        }
        return $options;
    }

}
