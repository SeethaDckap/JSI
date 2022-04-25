<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Message;

class Csns extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context
        );
    }



    public function execute()
    {
        $serial = $this->getRequest()->getParam('serial');
        $sku = $this->getRequest()->getParam('sku');
        $productId = $this->getRequest()->getParam('product_id');
        $mode = $this->getRequest()->getParam('mode');

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $found = false;

        if ($serial && $helper->isMessageEnabled('epicor_comm', 'csns')) {

            if (empty($sku) && $productId) {
                $product = $this->catalogProductFactory->create()->load($productId);
                /* @var $product Epicor_Comm_Model_Product */
                $sku = $helper->getSku($product->getSku());
            }

            $searches = array(
                'serial_number' => array(
                    'EQ' => $serial
                ),
                'product_code' => array(
                    'EQ' => $sku
                ),
            );

            $search = $helper->sendErpMessage('epicor_comm', 'csns', array(), $searches);

            if ($search['success']) {
                $message = $search['message'];

                $response = $message->getResponse();



                if ($response) {
                    $productsGroup = $response->getProducts();
                    $products = ($productsGroup) ? $productsGroup->getasarrayProduct() : array();
                    if (!empty($products)) {
                        $found = true;
                    }
                }
            }
        }

        if ($mode == 'validate') {
            $response = ($found) ? 'VALID' : 'INVALID';
        } else {
            // TBC: not actually needed at the mo, but may need an ajxable csns that returns results
            $response = '';
        }

        $this->getResponse()->setBody($response);
    }

    }
