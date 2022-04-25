<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class Configureproduct extends \Epicor\Comm\Controller\Returns
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProductHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->request = $request;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->commProductHelper = $commProductHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry);
    }


public function execute()
    {
        $productId = $this->request->getParam('productid');
        $child = $this->request->getParam('child');
        $options = $this->request->getParam('options');

        if ($child) {
            $childProd = $this->catalogProductFactory->create()->load($child);
            $this->registry->register('child_product', $childProd);
        }

        if ($options) {
            $options = unserialize(base64_decode($options));
            $optionsData = array();
            foreach ($options as $option) {
                $optionsData[$option['description']] = $option['value'];
            }
            $this->registry->register('options_data', $optionsData);
        }

        $product = $this->catalogProductHelper->initProduct($productId, $this);
        /* @var $product Mage_Core_Model_Generic */

        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $block = $this->getLayout()->createBlock('epicor_comm/catalog_product_configure');
        /* @var $block Epicor_Comm_Block_Catalog_Product_Configure */

        $response = array(
            'error' => '',
            'html' => $block->toHtml(),
            //M1 > M2 Translation Begin (Rule p2-7)
            //'jsonconfig' => Mage::helper('core')->jsonDecode($block->getJsonConfig())
            'jsonconfig' => $this->jsonHelper->jsonEncode($block->getJsonConfig())
            //M1 > M2 Translation End
        );

        $this->getResponse()->setBody(json_encode($response));
    }

    }
