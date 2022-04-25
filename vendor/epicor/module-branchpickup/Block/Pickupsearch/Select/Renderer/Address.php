<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer;


class Address extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();


    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    //   protected $updateList = Array();
public function __construct(
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper
    ) {
        $this->branchPickupHelper = $branchPickupHelper;
    }
        public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->branchPickupHelper;
        /* var Epicor_BranchPickup_Helper_Data  */
        $getData = $helper->getPickupAddress($row->getCode());
        $jsonArray = json_encode($getData);
        $html = '<input type="text" id="branchpickup_' . trim($row->getCode()) . '" class="branchsearchdetails" name="branchsearchdetails"';
        $html .= ' style="display:none" value="' . htmlspecialchars($jsonArray) . '"/> ';
        $html .= $row->getId();
        return $html;
    }

}
