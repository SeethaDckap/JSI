<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Grid;

class Clear extends \Epicor\Lists\Controller\Grid
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->customerSession = $customerSession;
        $this->cache = $cache;
        $this->request = $request;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
    }
    /**
     * Clear action - clears the cache for the specified grid
     */
    public function execute()
    {

        $customerId = $this->customerSession->getCustomer()->getId();

        $grid = $this->request->getParam('grid');
        $location = $this->frameworkHelperDataHelper->urlDecode($this->request->getParam('location'));

        $tags = array('CUSTOMER_' . $customerId . '_CUSTOMERCONNECT_' . strtoupper($grid));

        //M1 > M2 Translation Begin (Rule p2-6.7)
        //$cache = Mage::app()->getCacheInstance();
        $cache = $this->cache;
        //M1 > M2 Translation End
        /* @var $cache Mage_Core_Model_Cache */
        $cache->clean($tags);

        $this->_redirectUrl($location);
    }

}
