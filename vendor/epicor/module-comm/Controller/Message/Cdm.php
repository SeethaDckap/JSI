<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Message;

class Cdm extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CdmFactory
     */
    protected $commMessageRequestCdmFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory
    ) {
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->request = $request;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->storeManager = $storeManager;
        $this->commMessageRequestCdmFactory = $commMessageRequestCdmFactory;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $helper = $this->commConfiguratorHelper;
        /* @var $helper Epicor_Comm_Helper_Configurator */

        $ewaCode = $this->request->getParam('EWACode');
        $groupSequence = $this->request->getParam('groupSequence');
        $qty = $this->request->getParam('qty');
        $productSku = $this->request->getParam('SKU');
        $error = '';

        try {
            $product = $this->catalogProductFactory->create();
            /* @var $product Epicor_Comm_Model_Product */

            $product->setStoreId($this->storeManager->getStore()->getId())
                ->load($product->getIdBySku($productSku));

            $prodArray = array();

            $cdm = $this->commMessageRequestCdmFactory->create();
            /* @var $cdm Epicor_Comm_Model_Message_Request_Cdm */

            $cdm->setProductSku($product->getSku());
            $cdm->setProductUom($product->getEccUom());
            $cdm->setTimeStamp(null);

            $cdm->setQty(1);

            if (!empty($ewaCode)) {
                $cdm->setEwaCode($ewaCode);
            }

            if (!empty($groupSequence)) {
                $cdm->setGroupSequence($groupSequence);
            }

            if ($cdm->sendMessage()) {
                $configurator = $cdm->getResponse()->getConfigurator();

                $prodArray = array(
                    'name' => $product->getName(),
                    'ewa_code' => $configurator->getEwaCode(),
                    'ewa_description' => $configurator->getShortDescription(),
                    'ewa_short_description' => $configurator->getShortDescription(),
                    'ewa_sku' => $configurator->getConfiguredProductCode(),
                    'ewa_title' => $configurator->getTitle()
                );
            } else {
                $error = __('Failed to retrieve configured details.');
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $response = array(
            'error' => $error,
            'product' => $prodArray
        );

        $this->getResponse()->setBody(json_encode($response));
    }

    }
