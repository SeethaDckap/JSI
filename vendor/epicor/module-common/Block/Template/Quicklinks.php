<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Template;


/**
 * 
 * Template link override block
 * 
 *  - adds access check to link display
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team 
 */
//class Quicklinks extends \Epicor\FlexiTheme\Block\Frontend\Template\Quicklinks
class Quicklinks extends \Magento\Framework\View\Element\Html\Links
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $storeGroupFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Epicor\Common\Helper\Access $commonAccessHelper
    ) {
        $this->storeManager = $storeManager;
        $this->storeGroupFactory = $storeGroupFactory;
        $this->layout = $layout;
        $this->commonAccessHelper = $commonAccessHelper;
    }
    protected function _beforeToHtml()
    {
        //ecc_storeswitcher variables
        $groups = $this->storeManager->getWebsite()->getGroups();
        $currentGroupId = $this->storeManager->getStore()->getGroupId();
        $useStoreSwitcher = $this->storeGroupFactory->create()->load($currentGroupId)->getEccStoreswitcher();
        $websiteId = $this->storeManager->getWebsite()->getId();
        //M1 > M2 Translation Begin (Rule p2-1)
        //$storesToSelect = Mage::getModel('core/store_group')->addFieldToFilter('website_id', array('eq' => $websiteId))
        //    ->addFieldToFilter('ecc_storeswitcher', array('eq' => true))
        //    ->count();
        $storesToSelect = $this->storeGroupFactory->create()->addFieldToFilter('website_id', array('eq' => $websiteId))
            ->addFieldToFilter('ecc_storeswitcher', array('eq' => true))
            ->count();
        //M1 > M2 Translation End
        if ($this->_origBlock) {
            $quicklinks = $this->layout->getBlock($this->_origBlock);
            if ($quicklinks) {
                $this->_links = $quicklinks->getLinks();
            }
        }

        $helper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */
        foreach ($this->_links as $x => $link) {
            $url = ($link['url']) ?: '';
            if (!empty($url) && !$helper->canAccessUrl($url)) {
                unset($this->_links[$x]);
            }
            if ($link['title'] == 'brandselect' && (!$useStoreSwitcher || $storesToSelect < 2)) {          // don't display brand select link, if not required
                unset($this->_links[$x]);
            }
        }

        $this->_linksSet = true;
        parent::_beforeToHtml();
    }

}
