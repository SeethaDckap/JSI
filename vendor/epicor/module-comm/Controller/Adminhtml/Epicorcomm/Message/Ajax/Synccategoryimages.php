<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class Synccategoryimages extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Epicor\Comm\Helper\Catalog\Category\Image\Sync
     */
    protected $commCatalogCategoryImageSyncHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Epicor\Comm\Helper\Catalog\Category\Image\Sync $commCatalogCategoryImageSyncHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->commCatalogCategoryImageSyncHelper = $commCatalogCategoryImageSyncHelper;
        $this->response = $context->getResponse();
        parent::__construct(
            $context,
            $backendAuthSession,    
            $commCustomerSkuFactory);
    }

    /**
     * Processes Images Sync from Erp to Magento for a specific category
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category');
        
        if ($categoryId) { 
            $category = $this->catalogCategoryFactory->create()->setStoreId(0)->load($categoryId); 
            /* @var $category Mage_Catalog_Model_Category */
            if (!$category->isObjectNew()) {
                $helper = $this->commCatalogCategoryImageSyncHelper;
                /* @var $helper Epicor_Comm_Helper_Catalog_Category_image_sync */
                $helper->processErpImages($category, true);
            } 
        }

        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody('true');
        
        $this->response->setBody('true');
        
        //M1 > M2 Translation End
    }

    }
