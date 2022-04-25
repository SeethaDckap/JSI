<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

class Loadewa extends \Epicor\Comm\Controller\Configurator
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

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CimFactory
     */
    protected $commMessageRequestCimFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Message\Request\CimFactory $commMessageRequestCimFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->request = $request;
        $this->registry = $registry;
        $this->commMessageRequestCimFactory = $commMessageRequestCimFactory;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $helper = $this->commConfiguratorHelper;
        /* @var $helper Epicor_Comm_Helper_Configurator */

        $this->loadLayout();

        $productId = $this->request->getParam('productId');
        $return = $this->request->getParam('return');
        $location = $this->request->getParam('location');
        $address = $this->request->getParam('address');
        $quoteId = $this->request->getParam('quoteId');
        $lineNumber = $this->request->getParam('lineNumber');

        $this->registry->register('EWAReturn', $return);
        $this->registry->register('location_code', $location);

        $product = $this->catalogProductFactory->create()->load($productId);
        $cim = $this->commMessageRequestCimFactory->create();
        /* @var $cim Epicor_Comm_Model_Message_Request_Cim */
        $cim->setProductSku($product->getSku());
        $cim->setProductUom($product->getEccUom());
        $cim->setQuoteId(!empty($quoteId) ? $quoteId : null);
        $cim->setLineNumber($lineNumber);
        $cim->setDeliveryAddress($helper->getDeliveryAddressFromRFQ($address));
        $cim->sendMessage();

        $cimData = array(
            'quote_id' => $cim->getQuoteId(),
            'line_number' => $cim->getLineNumber(),
        );

        if ($cim->isSuccessfulStatusCode())
            $this->registry->register('EWAData', $cim->getResponse()->getConfigurator());
        $this->registry->register('EWASku', $product->getSku());
        $this->registry->register('CIMData', $this->dataObjectFactory->create($cimData));

        $this->renderLayout();
    }

    }
