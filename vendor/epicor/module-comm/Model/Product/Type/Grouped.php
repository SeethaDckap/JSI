<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Product\Type;


class Grouped extends \Magento\GroupedProduct\Model\Product\Type\Grouped
{

    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $bundleOptionFactory;

    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Framework\App\State $appState,
        \Magento\Msrp\Helper\Data $msrpData,
        \Magento\Bundle\Model\OptionFactory $bundleOptionFactory
    )
    {
        $this->bundleOptionFactory = $bundleOptionFactory;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $catalogProductLink,
            $storeManager,
            $catalogProductStatus,
            $appState,
            $msrpData
        );
    }


    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_array($result)) {
            foreach ($result as $product) {
                /* @var $product Epicor_Comm_Model_Product */
                if ($product->getTypeId() == 'bundle') {
                    if ($buyRequest->getBundleOption()) {
                        $infoBuyRequest = unserialize($product->getCustomOption('info_buyRequest')->getValue());
                        $infoBuyRequest['bundle_option'] = array();

                        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
                        $optionsCollection = $this->getOptionsCollection($product);
                        $productOptionIds = $optionsCollection->getAllIds();
                        foreach ($buyRequest->getBundleOption() as $optionId) {
                            if (in_array($optionId, $productOptionIds)) {
                                $infoBuyRequest['bundle_option'][$optionId] = $optionId;
                            }
                        }

                        $product->addCustomOption('info_buyRequest', serialize($infoBuyRequest));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve bundle option collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptionsCollection($product = null)
    {
        $optionsCollection = $this->bundleOptionFactory->create()->getResourceCollection()
            ->setProductIdFilter($this->getProduct($product)->getId())
            ->setPositionOrder();

        $storeId = $this->getStoreFilter($product);
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }

        $optionsCollection->joinValues($storeId);
        return $optionsCollection;
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isStrictProcessMode
     * @return array|string
     */
    protected function getProductInfo(\Magento\Framework\DataObject $buyRequest, $product, $isStrictProcessMode)
    {
        $productsInfo = $buyInfo = $buyRequest->getSuperGroup() ?: [];
        $associatedProducts = $this->getAssociatedProducts($product);

        if (!is_array($productsInfo)) {
            return __('Please specify the quantity of product(s).')->render();
        }
        foreach ($associatedProducts as $subProduct) {
            if (!isset($productsInfo[$subProduct->getId()])) {
                if ($isStrictProcessMode && $subProduct->getQty() < 0) {
                    return __('Please specify the quantity of product(s).')->render();
                }
                $productsInfo[$subProduct->getId()] = (in_array($subProduct->getId(), $buyInfo)) ? $subProduct->getQty() : 0;
            }
        }

        return $productsInfo;
    }
    
    /**
     * Retrieve array of associated products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAssociatedForDeletion($product) {
        if (!$product->hasData($this->_keyAssociatedProducts)) {
            $associatedProducts = [];

            $this->setSaleableStatus($product);

            $collection = $this->getAssociatedProductCollection(
                            $product
                    )->addAttributeToSelect(
                            ['name', 'price', 'special_price', 'special_from_date', 'special_to_date', 'status']
                    )->addFilterByRequiredOptions()->setPositionOrder()->addStoreFilter(
                    $this->getStoreFilter($product)
            );

            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $product->setData($this->_keyAssociatedProducts, $associatedProducts);
        }
        return $product->getData($this->_keyAssociatedProducts);
    }

}
