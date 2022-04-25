<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Store;

class Selector extends \Epicor\Comm\Controller\Store
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeStoreFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;
    
     /**
     * @var \Magento\Framework\View\Result\PageFactory 
     */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeStoreFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->storeStoreFactory = $storeStoreFactory;
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context
        );
    }



    public function execute()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */


        //   $stores = $helper->getBrandSelectStores();
        $stores = $helper->getSelectableStores();
        $storeCount = count($stores);

        switch ($storeCount) {
            case 0:
                $this->generic->addError('No Stores available for this user on this site, unable to log in');
                //M1 > M2 Translation Begin (Rule 58)
                //$url = Mage::helper('customer')->getLogoutUrl();
                $url = $this->customerUrl->getLogoutUrl();
                //M1 > M2 Translation End
                //M1 > M2 Translation Begin (Rule p2-3)
                //Mage::app()->getResponse()->setRedirect($url);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($url);
                return $resultRedirect;
                //M1 > M2 Translation End
                break;
            case 1:
                $storeId = array_keys($stores);
                $store_code = $this->storeManager->getGroup($storeId[0]);

                $code = $this->storeStoreFactory->create()->load($store_code->getDefaultStoreId())->getCode();
                $this->customerSession->setHasStoreSelected(true);
                //M1 > M2 Translation Begin (Rule p2-3)
                //Mage::app()->getResponse()->setRedirect(Mage::getUrl('', array('_query' => array('___store' => $code))));
                $resultRedirect = $this->resultRedirectFactory->create();
                //M1 > M2 Translation Begin (Rule p2-4)
                //$resultRedirect->setUrl(Mage::getUrl('', array('_query' => array('___store' => $code))));
                $resultRedirect->setUrl($this->_url->getUrl('', array('_query' => array('___store' => $code))));
                //M1 > M2 Translation End
                return $resultRedirect;
                //M1 > M2 Translation End
                break;
            default:
                //$this->loadLayout();
                $resultPage = $this->resultPageFactory->create();
                //$this->renderLayout();
                return $resultPage;
                break;
        }

        //--SF the original code is below, this can be removed if the above is ok
//        
//        if ($storeCount <= 1) {
//            if (!Mage::getSingleton('customer/session')->getHasStoreSelected()) {
//                Mage::getSingleton('customer/session')->setHasStoreSelected(true);
//            }
//            
//            //
//            $website = Mage::app()->getWebsite();
//            $store = $website->getDefaultStore();
//            Mage::app()->getResponse()->setRedirect(Mage::getUrl('', array('_query' => array('___store' => $store->getCode()))));
//            
////            Mage::getSingleton('core/session')->addError('No Stores available for this user on this site, unable to log in');
////            $url = Mage::helper('customer')->getLogoutUrl();
////            Mage::app()->getResponse()->setRedirect($url);  
//        } else {
//            $this->loadLayout();
//            $this->renderLayout();
//        }
    }

    }
