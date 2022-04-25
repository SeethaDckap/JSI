<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Store;

class Select extends \Epicor\Comm\Controller\Store
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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $storeGroupFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commHelper = $commHelper;
        $this->request = $request;
        $this->storeGroupFactory = $storeGroupFactory;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $storeId = $this->request->getParam('store');

        $stores = $helper->getBrandSelectStores();
        $storeIds = array_keys($stores);
        $store = $this->storeGroupFactory->create()->load($storeId);

        if (in_array($storeId, $storeIds) && !$store->isObjectNew()) {

            $customerSession = $this->customerSession;
            $customerSession->setHasStoreSelected(true);
            $view = $store->getDefaultStore();
            if ($view) {
                $path = '';
                $customer = $customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */
                if ($customer->isSupplier() || $customer->isSalesRep()) {
                    $path = 'customer/account';
                }
                //M1 > M2 Translation Begin (Rule p2-4)
                //$url = Mage::getUrl($path, array('_query' => array('___store' => $view->getCode())));
                $url = $this->_url->getUrl($path, array('_query' => array('___store' => $view->getCode())));
                //M1 > M2 Translation End
            } else {
                $this->messageManager->addError('Selected Store has no valid store view. Unable to continue');
                //M1 > M2 Translation Begin (Rule p2-4)
                //$url = Mage::getUrl('epicor_comm/store/selector');
                $url = $this->_url->getUrl('epicor_comm/store/selector');
                //M1 > M2 Translation End
            }
        } else {
            $this->customerSession->setHasStoreSelected(false);
            $this->messageManager->addError('Invalid Store Choice, Please Select a Valid Store');
            //M1 > M2 Translation Begin (Rule p2-4)
            //$url = Mage::getUrl('epicor_comm/store/selector');
            $url = $this->_url->getUrl('epicor_comm/store/selector');
            //M1 > M2 Translation End
        }

        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setRedirect($url);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($url);
        return $resultRedirect;
        //M1 > M2 Translation End
    }

}
