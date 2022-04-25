<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Config\Backend\Address;


class Street
{

    
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;


    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterAfterDelete(
        \Magento\Customer\Model\Config\Backend\Address\Street $subject,
        $result
    ) {
       $attribute = $this->_eavConfig->getAttribute('customer_address', 'street');
            $attribute->setData('multiline_count', 2);
            $attribute->save();
        return $result;
    }
    
    
    

    
}