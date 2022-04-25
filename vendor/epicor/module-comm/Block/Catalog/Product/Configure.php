<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product;


/**
 * Configure product popup block
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Configure extends \Magento\Catalog\Block\Product\View
{

    private $_status;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Epicor\Comm\Helper\Product $commProductHelper,
        array $data = []
    ) {
        $this->registry = $context->getRegistry();
        $this->commProductHelper = $commProductHelper;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/catalog/product/configure.phtml');
    }

    public function getProductOptionsHtml()
    {
        $product = $this->registry->registry('current_product');
        /* @var $product Epicor_Comm_Model_Product */

        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $child = $this->registry->registry('child_product');
        /* @var $child Epicor_Comm_Model_Product */

        $options = $this->registry->registry('options_data');

        return $helper->getProductOptionsHtml($product, $child, $options);
    }

    //M1 > M2 Translation Begin (Rule p2-8)
    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    //M1 > M2 Translation End

}
