<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Request\Operations;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Class Create
 *
 * @package Epicor\Punchout\Model\Request\Operations
 */
class CartOperation
{

    /**
     * ProductRepository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Data.
     *
     * @var array
     */
    protected $data;

    /**
     * CartOperation constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array                                           $data
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        array $data=[]
    ) {
        $this->productRepository = $productRepository;
        $this->data              = $data;

    }//end __construct()


    /**
     * @param     $item
     * @param int $storeId
     *
     * @return null|\Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductData($item, int $storeId)
    {
        if (empty((string) $item->ItemID->SupplierPartID)) {
            return null;
        }

        $sku     = (string) $item->ItemID->SupplierPartID;
        $product = $this->productRepository->get($sku, false, $storeId, true);
        if ($product->getTypeId() === Grouped::TYPE_CODE) {
            // Get the child sku by appending uomsepartor and uom.
            $uomSeparator = $this->getCommonHelper()->getUOMSeparator();
            $uom          = $item->ItemDetail->UnitOfMeasure;
            $childSku     = $product->getSku().$uomSeparator.$uom;
            $product      = $this->productRepository->get($childSku, false, $storeId, true);
        }

        return $product;

    }//end getProductData()


    /**
     * @return \Magento\Quote\Api\CartRepositoryInterface
     */
    public function getCartRepository()
    {
        return $this->data['cartRepositoryObj'];

    }//end getCartRepository()


    /**
     * @return \Magento\Quote\Api\CartManagementInterface
     */
    public function getCartManagementInterface()
    {
        return $this->data['cartManagementObj'];
    }

    public function getCustomerRepository()
    {
        return $this->data['customerRepositoryObj'];
    }

    /**
     * @return \Epicor\Common\Helper\Data|\Epicor\Punchout\Model\Request\Operations\CommonHelper
     */
    public function getCommonHelper()
    {
        return $this->data['commonHelperObj'];
    }
    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->data['storeManagerObj'];
    }


}//end class
