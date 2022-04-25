<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

class Reorderewa extends \Epicor\Comm\Controller\Configurator
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

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->request = $request;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $helper = $this->commConfiguratorHelper;
        /* @var $helper Epicor_Comm_Helper_Configurator */

        $productId = $this->request->getParam('productId');
        $groupSequence = $this->request->getParam('groupSequence');

        $helper->reorderProduct($productId, $groupSequence);

        $this->_redirect('checkout/cart');
    }

    }
