<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


class Gortriggers
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;

    }

    public function toOptionArray()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$triggers = (array) Mage::getConfig()->getNode('adminhtml/gor_triggers');
        $triggers = (array) $this->_scopeConfig->getValue('epicor_comm_mapping/gor_triggers');
        //M1 > M2 Translation End
        $triggerArray=array();
        foreach ($triggers as $value => $label) {
            $triggerArray[] = array('value' => $value, 'label' => __($label));
        }
        return $triggerArray;
    }

}
