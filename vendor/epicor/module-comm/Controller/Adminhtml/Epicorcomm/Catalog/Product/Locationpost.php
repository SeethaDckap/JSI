<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Catalog\Product;

class Locationpost extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Catalog\Product
{

    /**
     * @var \Epicor\Comm\Model\Location\ProductFactory
     */
    protected $commLocationProductFactory;

    /**
     * @var \Epicor\Comm\Model\Location\Product\CurrencyFactory
     */
    protected $commLocationProductCurrencyFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Location\ProductFactory $commLocationProductFactory,
        \Epicor\Comm\Model\Location\Product\CurrencyFactory $commLocationProductCurrencyFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commLocationProductFactory = $commLocationProductFactory;
        $this->commLocationProductCurrencyFactory = $commLocationProductCurrencyFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if (isset($data['manufacturers'])) {
            $manData = array();
            $first = true;
            foreach ($data['manufacturers'] as $manRowData) {
                if (!empty($manRowData['name']) && !empty($manRowData['product_code'])) {
                    if ($first) {
                        $manRowData['primary'] = 'Y';
                    } else {
                        $manRowData['primary'] = 'N';
                    }
                    $manData[] = $manRowData;
                    $first = false;
                }
            }
            $data['manufacturers'] = serialize($manData);
        }
        if (isset($data['id'])  && $data['id'] == '') {
            unset($data['id']);
            $productLocation = $this->commLocationProductFactory->create();
        }else{
            $productLocation = $this->commLocationProductFactory->create()->load($data['id']);
        }
        /* @var $productLocation Epicor_Comm_Model_Location_Product */
        $productLocation->setData($data);

        $currencyData = $productLocation->getCurrency($data['currency_code']);
        /* @var $currencyData Epicor_Comm_Model_Location_Product_Currency */
        if ($currencyData === false) {
            $currencyData = $this->commLocationProductCurrencyFactory->create();
            $currencyData->setProductId($productLocation->getProductId());
            $currencyData->setLocationCode($productLocation->getLocationCode());
            $currencyData->setCurrencyCode($data['currency_code']);
        }
        $currencyData->setBasePrice($data['base_price']);
        $currencyData->setCostPrice($data['cost_price']);

        $productLocation->setCurrency($currencyData);
        $productLocation->save();
    }

    }
