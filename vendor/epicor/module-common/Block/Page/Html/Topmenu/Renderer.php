<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Page\Html\Topmenu;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Renderer extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->commonHelper = $commonHelper;
        $this->commonAccessHelper = $commonAccessHelper;
    }
    public function getCacheKeyInfo()
    {
        $shortCacheId = array(
            'TOPMENU',
            $this->storeManager->getStore()->getId(),
            //M1 > M2 Translation Begin (Rule p2-5.4)
            /*Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),*/
            $this->_design->getDesignTheme()->getCode(),
            $this->_design->getDesignTheme()->getThemePath(),
            //M1 > M2 Translation End
            $this->customerSession->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrentEntityKey()
        );

        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */

        $extraKeys = $helper->getCategoryCacheKeys();
        $shortCacheId = array_merge($shortCacheId, $extraKeys);
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['entity_key'] = $this->getCurrentEntityKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    private function hasAccess()
    {
        $accessHelper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */

        return $accessHelper->canAccessUrl('catalog/category/view');
    }

    protected function _toHtml()
    {
        $html = '';

        if ($this->hasAccess()) {
            $html = parent::_toHtml();
        }

        return $html;
    }

}
