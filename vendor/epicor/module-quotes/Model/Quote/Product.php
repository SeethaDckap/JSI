<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\Quote;


/**
 * 
 * @method int getId()
 * @method int getQuoteId()
 * @method int setQuoteId(int $value)
 * @method int getProductId()
 * @method int setProductId(int $value)
 * @method int getOrigQty()
 * @method int setOrigQty(int $value)
 * @method float getOrigPrice()
 * @method float setOrigPrice(float $value)
 * @method int getNewQty()
 * @method int setNewQty(int $value)
 * @method float getNewPrice()
 * @method float setNewPrice(float $value)
 * @method string getNote()
 * @method string setNote(string $value)
 * @method string getErpNoteRef()
 * @method string setErpNoteRef(string $value)
 * @method string getErpLineNumber()
 * @method string setErpLineNumber(string $value)
 * @method string getLocationCode()
 * @method string setLocationCode(string $value)
 * 
 */
class Product extends \Epicor\Database\Model\Quote\Product
{

    protected $_product;
    protected $_options;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->catalogProductFactory = $catalogProductFactory;
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
        $this->_init('Epicor\Quotes\Model\ResourceModel\Quote\Product');
    }

    /**
     * 
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_product)
            $this->_product = $this->catalogProductFactory->create()->load($this->getProductId());
        return $this->_product;
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * 
     * @return string
     */
    public function getSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * 
     * @return string
     */
    public function getUom()
    {
        return $this->getProduct()->getEccUom();
    }

    /**
     * Gets the products options
     * 
     * @return string
     */
    public function getProductOptions()
    {
        if (empty($this->_options)) {
            if ($this->getOptions()) {
                $this->_options = unserialize($this->getOptions());
            }
        }

        return $this->_options;
    }

}
