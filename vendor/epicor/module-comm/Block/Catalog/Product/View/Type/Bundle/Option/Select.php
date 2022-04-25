<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Type\Bundle\Option;

/**
 * Bundle option dropdown type renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Select extends \Epicor\Comm\Block\Catalog\Product\View\Type\Bundle\Option
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $productTypeGroupedFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GroupedProduct\Model\Product\Type\GroupedFactory $grouped,
        array $data = [])
    {
        $this->productFactory = $productFactory;
        $this->productTypeGroupedFactory = $grouped;
        parent::__construct($context, $jsonEncoder, $catalogData, $registry, $string, $mathRandom, $cartHelper, $taxData, $pricingHelper, $data);
    }


    /**
     * Set template
     *
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate('epicor_comm/catalog/product/view/type/bundle/option/select.phtml');
        parent::_construct();
    }

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getProduct()
    {
        return $this->productFactory;
    }

    public function getGrouped()
    {
        return $this->productTypeGroupedFactory;
    }
    //M1 > M2 Translation End


}
