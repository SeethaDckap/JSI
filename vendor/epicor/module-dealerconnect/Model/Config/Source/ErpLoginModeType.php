<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Config\Source;


class ErpLoginModeType
{
   protected $commonHelper;
    
    public function __construct(
        \Epicor\Common\Helper\Data $commonHelper
    ) {
        $this->commonHelper = $commonHelper;
    }

    
     public function toOptionArray()
    {
        $helper = $this->commonHelper;
        $dealerLicense = $helper->checkDealerLicense();       
        $options = array(
            array('value' => '2', 'label' => 'Global Default'),
            array('value' => 'dealer', 'label' => 'Dealer'),
            array('value' => 'shopper', 'label' => 'End Customer'),
        );
        //If thee is no dealer License then remove that
        if(!$dealerLicense) {
            unset($options[1]);
        }     
        return $options;
    }

}
