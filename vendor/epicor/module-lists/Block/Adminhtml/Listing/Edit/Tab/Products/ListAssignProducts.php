<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products;


class ListAssignProducts extends \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products
{
    /**
     * @var \Epicor\Lists\Model\ResourceModel\Product\Collection
     */
    private $listProducts;

    /**
     * ListAssignProducts constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Lists\Helper\Data $listsHelper
     * @param \Epicor\Common\Helper\Locale\Format\Currency $commonLocaleFormatCurrencyHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Lists\Model\ListModelFactory $listsListModelFactory
     * @param \Epicor\Lists\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper
     * @param \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\SkunodelimiterFactory $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory
     * @param \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\ContractquantitiesFactory $listsAdminhtmlListingEditTabRendererContractquantitiesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Common\Helper\Locale\Format\Currency $commonLocaleFormatCurrencyHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\SkunodelimiterFactory $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory,
        \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\ContractquantitiesFactory $listsAdminhtmlListingEditTabRendererContractquantitiesFactory,
        array $data = []
    ){
        parent::__construct(
            $context,
            $backendHelper,
            $listsHelper,
            $commonLocaleFormatCurrencyHelper,
            $registry, $listsListModelFactory,
            $catalogResourceModelProductCollectionFactory,
            $catalogProductType,
            $listsMessagingCustomerHelper,
            $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory,
            $listsAdminhtmlListingEditTabRendererContractquantitiesFactory,
            $data
        );
        $this->listProducts = $catalogResourceModelProductCollectionFactory->create();
    }

    /**
     * @return false|string
     */
    public function getAssignedProducts()
    {
        $this->listProducts->setListProductsByPosition($this->getList());
        
        $entityIdPositions = [];
        foreach ($this->listProducts as $productPosition) {
            $entityIdPositions[$productPosition->getEntityId()] = $this->getPositionValue($productPosition);
        }

        return json_encode($entityIdPositions);
    }

    /**
     * @param $productPosition
     * @return int
     */
    private function getPositionValue($productPosition)
    {
        $position = $productPosition->getListPosition();
        if($position !== null){
            return $productPosition->getListPosition();
        }

        return null;
    }
}