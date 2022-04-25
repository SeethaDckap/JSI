<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Productpricing extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory
     */
    protected $listsResourceListModelProductCollectionFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listsResourceListModelProductCollectionFactory
    ) {
        $this->listsResourceListModelProductCollectionFactory = $listsResourceListModelProductCollectionFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $listId = $this->getRequest()->getParam('list');
        $productId = $this->getRequest()->getParam('product');
        $listProduct = $this->listsResourceListModelProductCollectionFactory->create();
        /* @var $listProduct Epicor_Lists_Model_Resource_List_Product_Collection */
        $listProduct->addFieldToFilter('list_id', $listId);
        $listProduct->addFieldToFilter('sku', $productId);

        $product = $listProduct->getFirstItem();
        /* @var $item Epicor_Lists_Model_ListModel_Product */

        $pricing = array();
        foreach ($product->getPricing() as $item) {
            /* @var $item Epicor_Lists_Model_ListModel_Product_Price */
            $pricing[$item->getId()] = array(
                'id' => $item->getId(),
                'currency' => $item->getCurrency(),
                'price' => $item->getPrice(),
                'price_breaks' => $item->getPriceBreaks(),
                'value_breaks' => $item->getValueBreaks()
            );
        }


        $this->getResponse()->setBody(json_encode($pricing, JSON_FORCE_OBJECT));
    }

    }
