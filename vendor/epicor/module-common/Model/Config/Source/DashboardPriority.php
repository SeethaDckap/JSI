<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Source;


class DashboardPriority
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Epicor\Common\Helper\Data $commonHelper
    ) {
        $this->commonHelper = $commonHelper;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $selectArray[] = array('value' => 'comm', 'label' => __('Std Account'));

        if ($this->commonHelper->isModuleOutputEnabled('Epicor_Customerconnect')) {
            $selectArray[] = array('value' => 'customerconnect', 'label' => __('Customerconnect'));
        }
         if ($this->commonHelper->isModuleOutputEnabled('Epicor_Dealerconnect')) {
            $selectArray[] = array('value' => 'dealerconnect', 'label' => __('Dealerconnect'));
        }
        $selectArray[] = array('value' => 'accounttypedashboard', 'label' => __('Account Type Dashboard'));
        return $selectArray;
    }

}
