<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Rfqs;

use Epicor\Customerconnect\Controller\Rfqs;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ImportProductCsv
 * @package Epicor\Customerconnect\Controller\Rfqs
 */
class ImportProductCsv extends Rfqs
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!empty($_FILES['import_product_csv_file']['tmp_name'])
            && !in_array($_FILES['import_product_csv_file']['type'],
                \Epicor\Comm\Helper\Data::CSV_APPLIED_FORMAT)
        ) {
            $message = 'Wrong File Type. Only CSV files are allowed.';
            return $this->redirectBack($message);
        }

        if (empty($_FILES['import_product_csv_file']['tmp_name'])) {
            $message = 'Please select a file before submitting or make sure filesize is less than or equal to '.ini_get('upload_max_filesize').'B';
            return $this->redirectBack($message);
        }

        $productHelper = $this->commProductHelper;
        /* @var $productHelper Epicor_Comm_Helper_Product */

        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $products = $helper->processProductCsvUpload($_FILES['import_product_csv_file']['tmp_name']);

        $prodArray = array();

        if (!empty($products['products'])) {
            $messenger = $this->commMessagingHelper;
            /* @var $helper Epicor_Comm_Helper_Messaging */

            $msqProds = array();
            $qty = array();
            foreach ($products['products'] as $x => $product) {
                $msqProds[$x] = $product['product_added'];
                $qty[$x] = $product['qty'];
            }

            $messenger->sendMsq($msqProds);

            foreach ($msqProds as $index => $product) {
                /* @var $product Epicor_Comm_Model_Product */
                $skuQty = (is_array($qty)) ? $qty[$index] : 1;
                $price = $product->unsFinalPrice()->getFinalPrice($skuQty);
                $formattedPrice = $helper->formatPrice($price, true, $this->storeManager->getStore()->getBaseCurrencyCode());
                $formattedTotal = $helper->formatPrice($price * $skuQty, true, $this->storeManager->getStore()->getBaseCurrencyCode());
                $product->setFormattedPrice($formattedPrice);
                $product->setFormattedTotal($formattedTotal);
                $product->setMsqQty($skuQty);
                $product->setQty($skuQty);
                $product->setUsePrice($price);
                $prodArray[] = $productHelper->getProductInfoArray($product);
            }
        }

        if (!empty($products['errors'])) {
            foreach ($products['errors'] as $error) {
                $this->messageManager->addErrorMessage($error);
            }
        }

        $response = array(
            'errors' => $products['errors'],
            'products' => $prodArray
        );

        $this->registry->register('line_add_json', json_encode($response));
        $result = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
             $result->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lineaddbyjs')->toHtml()
        );
    }

    /**
     * @param $message
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    private function redirectBack($message)
    {
        $this->messageManager->addErrorMessage($message);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
