<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Page\Html;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Footer extends \Magento\Theme\Block\Html\Footer
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $httpContext,
            $data
        );
    }


    public function getCacheKeyInfo()
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $extraKey = $customer->isSupplier() ? 'supplier' : ($customer->isSupplier() ? 'customer' : ($customer->isSalesRep() ? 'salesrep' : 'guest'));

        $keyInfo = array(
            'PAGE_FOOTER',
            $this->storeManager->getStore()->getId(),
            (int) $this->storeManager->getStore()->isCurrentlySecure(),
            //M1 > M2 Translation Begin (Rule p2-5.4)
            /*Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),*/
            $this->_design->getDesignTheme()->getCode(),
            $this->_design->getDesignTheme()->getThemePath(),
            //M1 > M2 Translation End
            $this->customerSession->isLoggedIn(),
            $extraKey
        );

        return $keyInfo;
    }

}
