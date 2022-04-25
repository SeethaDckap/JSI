<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Template;


class Quicklinks extends \Epicor\Common\Block\Template\Links
{

    public $_origBlock;
    public $_linksSet = false;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper
    ) {
        $this->layout = $layout;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
    }
    /**
     * Get base data from default quick-access Menu
     *
     * @return Mage_Page_Block_Template_Links
     */
    protected function _beforeToHtml()
    {
        if ($this->_origBlock && !$this->_linksSet) {
            $quicklinks = $this->layout->getBlock($this->_origBlock);
            if ($quicklinks)
                $this->_links = $quicklinks->getLinks();
        }
        //this is required because the observer has a zero value for teh $this->_links variable when it is hit
        $branchPickup = $this->checkIfBranchPickupLinkRequired();
        $this->_linksSet = true;
        return parent::_beforeToHtml();
    }

    public function checkIfBranchPickupLinkRequired()
    {
        //check if the links button is to be displayed for flexitheme 
        $branchHelper = $this->branchPickupHelper;
        $branchpickupEnabled = $branchHelper->isBranchPickupAvailable();
        //$isLoggedIn          = Mage::helper('customer')->isLoggedIn();
        $helperBranchPickup = $this->branchPickupBranchpickupHelper;

        if (!$branchpickupEnabled) {
            $url = $this->getUrl('branchpickup/pickup/select', $helperBranchPickup->issecure());
            $this->removeLinkByUrl($url);
        }
    }

}
