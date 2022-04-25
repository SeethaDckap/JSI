<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;


/**
 * ERP Account controller controller
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class Locations extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        parent::__construct(
            $context
        );
    }

protected function _massiveAddFromSku($skus = array(), $trigger = '')
    {
        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $configureProducts = $helper->massiveAddFromSku($skus, $trigger);

        if (isset($configureProducts['products']) && !empty($configureProducts['products'])) {
            if (count($configureProducts['products']) == 1) {
                $productId = array_pop($configureProducts['products']);
                $product = $this->catalogProductFactory->create()->load($productId);
                $this->_redirect($product->getProductUrl());
            } else {
                $helper->addConfigureListProducts($configureProducts['products']);
                $helper->addConfigureListQtys($configureProducts['qty']);
                $this->getRequest()->setParam('csv', 1);
                $this->messageManager->addErrorMessage('One or more products require configuration before they can be added to the Cart. See list below');
            //    $this->loadLayout()->renderLayout();
            }
        } else {
            $this->_redirect('checkout/cart');
        }
    }

}
