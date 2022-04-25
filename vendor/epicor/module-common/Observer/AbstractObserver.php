<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $request;

    protected $commonAccessHelper;

    protected $customerSession;

    protected $scopeConfig;

    protected $frameworkHelperDataHelper;

    protected $eventManager;

    protected $generic;

    protected $backendJsHelper;

    protected $commonAccessGroupCustomerFactory;

    protected $registry;

    protected $commonHelper;

    protected $backendAuthSession;

    protected $commCustomerErpaccountAddressFactory;

    protected $catalogProductFactory;

    protected $listsFrontendProductHelper;

    protected $catalogCategoryFactory;

    protected $header;

    protected $url;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Common\Helper\AccessFactory $commonAccessHelper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\DataFactory $commonHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Lists\Helper\Frontend\ProductFactory $listsFrontendProductHelper,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Framework\HTTP\Header $header,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->request = $request;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->eventManager = $eventManager;
        $this->generic = $generic;
        $this->backendJsHelper = $backendJsHelper;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        $this->backendAuthSession = $backendAuthSession;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->header = $header;
        $this->url = $url;
        $this->response = $response;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


    protected function checkCategoryMenu(&$menuTree)
    {
        $children = $menuTree->getChildren();

        foreach ($children as $x => $child) {
            /* @var $child Varien_Data_Tree_Node */
            if ($child->hasChildren()) {
                $this->checkCategoryMenu($child);
            }

            $catId = str_replace('category-node-', '', $child->getId());
            $productCollection = $this->filterCategoryCollection($catId);
            if (!$child->hasChildren() && $productCollection->getSize() == 0) {
                $menuTree->removeChild($child);
            }
        }
    }
    /**
     * Remove Items from the category menu if they have no products and auto hide is enabled
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function filterCategoryCollection($categoryId)
    {
        $category = $this->catalogCategoryFactory->create()->load($categoryId);
        /* @var $category Mage_Catalog_Model_Category */
        $productCollection = $category->getProductCollection();
        $productCollection->addAttributeToFilter('visibility', array('in' => array(2, 4)));

        $productCollection = $this->commonHelper->create()->performLocationProductFiltering($productCollection);
        $productCollection = $this->commonHelper->create()->performContractProductFiltering($productCollection);
        return $productCollection;
    }



}

