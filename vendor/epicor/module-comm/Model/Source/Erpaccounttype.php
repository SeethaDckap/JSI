<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Source;


/**
 * Class Message Types
 */
class Erpaccounttype implements \Magento\Framework\Option\ArrayInterface
{
    
    /**
     * @var array
     */
    protected $options; 

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @param CollectionFactory $log
     */
    public function __construct(
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper
    ) {
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {   
            $options[] = [];
            $types = $this->commonAccountSelectorHelper->getAccountTypes();
            foreach ($types as $value => $info) {
                $options[] = ['label' => __($info['label']), 'value' => $value];
            }
        return $options;
    }
}
