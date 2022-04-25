<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class AddProduct extends \Epicor\Comm\Controller\Returns
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

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
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory
    )
    {
        $this->catalogProductFactory = $catalogProductFactory;
        $this->jsonHelper  = $jsonHelper;
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
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $helper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */
            /* Do action stuff here */
            $errors = array();

            $productId = $this->getRequest()->getParam('productid', false);
            $sku = $this->getRequest()->getParam('sku', false);
            $qty = $this->getRequest()->getParam('qty', false);
            $uom = $this->getRequest()->getParam('uom', false);

            $lines = array();

            if ($sku === false) {
                $errors[] = __('SKU is Empty');
            }
            if ($qty === false) {
                $errors[] = __('Qty is Empty');
            }

            if (empty($errors)) {

                $product = $this->catalogProductFactory->create();
                /* @var $product Epicor_Comm_Model_Product */

                if (!empty($productId)) {
                    $product = $product->load($productId);
                }

                if ($product->isObjectNew()) {
                    $product = $helper->findProductBySku($sku, $uom);
                }

                $allowed = true;

                if ($product && $product->getDataSource() == 'erp' && !$helper->configHasValue('allow_skus_type', 'erp')) {
                    $allowed = false;
                }

                if ($product && $allowed) {
                    $sku = $helper->stripProductCodeUOM($product->getSku());

                    $lines[] = array(
                        'sku' => $sku,
                        'qty_returned' => $qty,
                        'uom' => $product->getEccUom(),
                        'source' => 'SKU',
                        'source_label' => 'SKU',
                        'source_data' => '',
                        'type_id' => $product->getTypeId(),
                        'entity_id' => $product->getId(),
                        'decimal_place' => $helper->getDecimalPlaces($product)
                    );
                } else if (!$product && $helper->configHasValue('allow_skus_type', 'custom')) {
                    $lines[] = array(
                        'sku' => $sku,
                        'qty_returned' => $qty,
                        'uom' => '',
                        'source' => 'SKU',
                        'source_label' => 'SKU',
                        'source_data' => '',
                        'type_id' => '',
                        'entity_id' => '',
                        'decimal_place' => $helper->getDecimalPlaces($product)
                    );
                } else {
                    $errors[] = __('Could not find product by SKU');
                }
            }

            if (empty($errors)) {
                $result = array('lines' => $lines);

                if (!$helper->checkConfigFlag('allow_mixed_return')) {
                    $result['hide_find_by'] = 1;
                }
            } else {
                if (!is_array($errors)) {
                    $errors = array($errors);
                }
                $result = array('errors' => $errors);
            }

            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
        }
    }

    }
