<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/**
 *  To include the branselector.js file dynamically
 *  in the branch select front end and the checkout page
 */

namespace Epicor\BranchPickup\Block;

class Head extends \Magento\Framework\View\Element\Template
{
    public $assetRepo;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\BranchPickup\Helper\Data 
     */ 
    protected $branchPickupHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        array $data = []
    ){
        $this->assetRepo = $context->getAssetRepository();
        $this->branchPickupHelper = $branchPickupHelper;
        $this->request = $request;
        return parent::__construct($context, $data);
    }

    /**
     * Add branselector.js to head section conditionally
     * @return boolean
     */

    public function addBranSelectorJs()
    {
        $actionName = $this->request->getFullActionName();
        $actionCheck = array(
        'epicor_branchpickup_pickup_select',
        'checkout_index_index'
        );

        $branchpickupEnabled = $this->branchPickupHelper->isBranchPickupAvailable();
        if ($branchpickupEnabled && (in_array($actionName, $actionCheck))) {

            return true;
        }
 
    }    
}
