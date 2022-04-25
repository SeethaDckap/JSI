<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model;


/**
 * Model Class for Contract
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getListId()
 * @method string getSalesRep()
 * @method string getContactName()
 * @method string getPurchaseOrderNumber()
 * 
 * @method setListId()
 * @method setSalesRep()
 * @method setContactName()
 * @method setPurchaseOrderNumber()
 */
class Contract extends \Epicor\Database\Model\Contract
{

    protected $_changes = array();

    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const KEY_PRODUCTS = 'products';

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory
     */
    protected $listsResourceListModelProductCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\Contract\Product\CollectionFactory
     */
    protected $listsResourceContractProductCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\ProductFactory
     */
    protected $listsListModelProductFactory;

    /**
     * @var \Epicor\Lists\Model\Contract\ProductFactory
     */
    protected $listsContractProductFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listsResourceListModelProductCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\Contract\Product\CollectionFactory $listsResourceContractProductCollectionFactory,
        \Epicor\Lists\Model\ListModel\ProductFactory $listsListModelProductFactory,
        \Epicor\Lists\Model\Contract\ProductFactory $listsContractProductFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->listsResourceListModelProductCollectionFactory = $listsResourceListModelProductCollectionFactory;
        $this->listsResourceContractProductCollectionFactory = $listsResourceContractProductCollectionFactory;
        $this->listsListModelProductFactory = $listsListModelProductFactory;
        $this->listsContractProductFactory = $listsContractProductFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\Contract');
    }

    public function addListProducts($products)
    {
        if (!is_array($products)) {
            $products = array($products);
        }

        foreach ($products as $product) {
            if ($product instanceof \Magento\Framework\DataObject) {
                $this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD][$product->getProductSku()] = $product;
            }
        }
        
        $this->_hasDataChanges = true;
    }

    public function afterSave()
    {
        parent::afterSave();

        $this->_saveProducts();
    }

    protected function _saveProducts()
    {
        if (isset($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD]) &&
            is_array($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD]) &&
            count($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD]) > 0) {
            $productsCollection = $this->listsResourceListModelProductCollectionFactory->create();
            /* @var $productsCollection Epicor_Lists_Model_Resource_List_Product_Collection */
            $productsCollection->addFieldtoFilter('list_id', $this->getListId());

            $listProducts = array();
            foreach ($productsCollection->getItems() as $item) {
                $listProducts[$item->getSku()] = $item;
            }

            $productsCollection = $this->listsResourceContractProductCollectionFactory->create();
            /* @var $productsCollection Epicor_Lists_Model_Resource_Contract_Product_Collection */
            $productsCollection->addFieldtoFilter('contract_id', $this->getId());

            $contractProducts = array();
            foreach ($productsCollection->getItems() as $item) {
                $contractProducts[$item->getListProductId()] = $item;
            }

            foreach ($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD] as $product) {
                if (isset($listProducts[$product->getProductSku()])) {
                    $listProduct = $listProducts[$product->getProductSku()];
                } else {
                    $listProduct = $this->listsListModelProductFactory->create();
                    /* @var $listProduct Epicor_Lists_Model_ListModel_Product */
                    $listProduct->setListId($this->getListId());
                    $listProduct->setSku($product->getProductSku());
                    $listProduct->save();
                }

                if (isset($contractProducts[$listProduct->getId()])) {
                    $contractProduct = $contractProducts[$listProduct->getId()];
                } else {
                    $contractProduct = $this->listsContractProductFactory->create();
                    /* @var $contractProduct Epicor_Lists_Model_Contract_Product */
                }

                $contractProduct->addData($product->getData());
                $contractProduct->setContractId($this->getId());
                $contractProduct->setListProductId($listProduct->getId());
                $contractProduct->save();
            }
        }
    }

}
