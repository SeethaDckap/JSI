<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

class Editewa extends \Epicor\Comm\Controller\Configurator
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
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->request = $request;
        $this->registry = $registry;
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
        $address = $this->request->getParam('address');

        $quoteId = $this->request->getParam('quoteId');
        $itemId = $this->request->getParam('itemId');

        $cimData = array(
            'ewa_code' => $this->request->getParam('ewaCode'),
            'group_sequence' => $this->request->getParam('groupSequence'),
            'quote_id' => !empty($quoteId) ? $quoteId : null,
            'line_number' => $this->request->getParam('lineNumber'),
            'delivery_address' => $helper->getDeliveryAddressFromRFQ($address),
            'item_id' => $itemId
        );

        $this->registry->register('EWAReturn', $return);

        $cim = $helper->sendCim($productId, $cimData);

        if ($cim->isSuccessfulStatusCode()) {
            $this->registry->register('EWAData', $cim->getResponse()->getConfigurator());
            $this->registry->register('EWASku', $cim->getProductSku());
            $this->registry->register('CIMData', $this->dataObjectFactory->create($cimData));
        }

        $this->renderLayout();
    }

    }
