<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;

use Epicor\Lists\Model\ResourceModel\ListModel\Product\Price\CollectionFactory;
use Magento\Framework\DataObjectFactory;
use Epicor\Lists\Model\ListModel\Product\PriceFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Model Class for List Product
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getListId()
 * @method string getSku()
 *
 * @method string setListId()
 * @method string setSku()
 */
class Product extends \Epicor\Database\Model\Lists\Product
{

    protected $_pricing = [];

    /**
     * PriceResourceCollectionFactory
     *
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\Price\CollectionFactory
     */
    protected $priceResourceCollectionFactory;

    /**
     * PriceFactory
     *
     * @var \Epicor\Lists\Model\ListModel\Product\PriceFactory
     */
    protected $listsListModelProductPriceFactory;

    /**
     * DataObjectFactory
     *
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\Model\Context $context                           Context.
     * @param \Magento\Framework\Registry      $registry                          Registry.
     * @param CollectionFactory                $priceResourceCollectionFactory    CollectionFactory.
     * @param PriceFactory                     $listsListModelProductPriceFactory PriceFactory.
     * @param DataObjectFactory                $dataObjectFactory                 DataObjectFactory.
     * @param AbstractResource|null            $resource                          AbstractResource.
     * @param AbstractDb|null                  $resourceCollection                AbstractDb.
     * @param array                            $data                              Data.
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $priceResourceCollectionFactory,
        PriceFactory $listsListModelProductPriceFactory,
        DataObjectFactory $dataObjectFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataObjectFactory                 = $dataObjectFactory;
        $this->priceResourceCollectionFactory    = $priceResourceCollectionFactory;
        $this->listsListModelProductPriceFactory = $listsListModelProductPriceFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

    }//end __construct()


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Product');

    }//end _construct()


    public function getPricing()
    {
        $pricing = $this->priceResourceCollectionFactory->create();
        /* @var $pricing \Epicor\Lists\Model\ResourceModel\ListModel\Product\Price\Collection */
        $pricing->addFieldToFilter('list_product_id', $this->getId());

        return (array) $pricing->getItems();

    }//end getPricing()


    public function setPricing($prices)
    {
        $this->_pricing = $prices;

    }//end setPricing()


    /**
     * AfterSave.
     *
     * @return Product|void
     */
    public function afterSave()
    {
        parent::afterSave();
        $this->_savePricing();

    }//end afterSave()


    protected function _savePricing()
    {
        $newPricing = [];
        foreach ($this->_pricing as $price) {
            /* @var $price \Epicor\Lists\Model\ListModel\Product\Price  */
            if (!$price instanceof \Magento\Framework\DataObject) {
                $price = $this->dataObjectFactory->create()->addData($price);
            }

            $newPricing[$price->getCurrency()] = $price;
        }

        foreach ($this->getPricing() as $price) {
            /* @var $price \Epicor\Lists\Model\ListModel\Product\Price  */
            if (isset($newPricing[$price->getCurrency()])) {
                $pricing = $newPricing[$price->getCurrency()];
                $price->setPrice($pricing->getPrice());
                $price->setPriceBreaks($pricing->getPriceBreaks());
                $price->setValueBreaks($pricing->getValueBreaks());
                $price->save();
                unset($newPricing[$price->getCurrency()]);
            } else {
                $price->delete();
            }
        }

        foreach ($newPricing as $pricing) {
            $price = $this->listsListModelProductPriceFactory->create();
            /* @var $price \Epicor\Lists\Model\ListModel\Product\Price */
            $price->setCurrency($pricing->getCurrency());
            $price->setPrice($pricing->getPrice());
            $price->setPriceBreaks($pricing->getPriceBreaks());
            $price->setValueBreaks($pricing->getValueBreaks());
            $price->setListProductId($this->getId());
            $price->save();
        }

    }//end _savePricing()


}
