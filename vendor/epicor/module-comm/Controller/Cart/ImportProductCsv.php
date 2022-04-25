<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

use Epicor\Comm\Controller\Cart;
use Epicor\Comm\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ImportProductCsv
 * @package Epicor\Comm\Controller\Cart
 */
class ImportProductCsv extends Cart
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (empty($_FILES['import_product_csv_file']['tmp_name'])) {
            $message = 'Please select a file before submitting or make sure filesize is less than or equal to '.ini_get('upload_max_filesize').'B';
            return $this->redirectBack($message);
        }

        if (!in_array($_FILES['import_product_csv_file']['type'],
                Data::CSV_APPLIED_FORMAT)
        ) {
            $message = 'Wrong File Type. Only CSV files are allowed.';
            return $this->redirectBack($message);
        }

        $emptyExistingCart = $this->getRequest()->getParam('replace_cart');

        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        //check if products still to be configured are still to be removed
        $emptyConfigureProducts = $this->getRequest()->getParam('remove_products_to_be_configured');
        if ($emptyConfigureProducts) {
            $helper->clearConfigureList();
        }

        $products = $helper->processProductCsvUpload($_FILES['import_product_csv_file']['tmp_name']);



        $configureProducts = array();
        if (!empty($products['products'])) {

            $configureProducts = $helper->addCsvProductToCart($products['products'], $emptyExistingCart);
        }

        if (isset($products['errors']['general'])) {
            $this->messageManager->addErrorMessage($products['errors']['general']);
        } else {
            $allErrors = $this->_checkoutSession->getQopErrors();
            if (count($products['errors']) > 0) {
                $allErrors = is_array($allErrors) ? array_merge($allErrors, $products['errors']) : $products['errors'];
            }
            if ($allErrors !== null && count($allErrors) > 1) {
                $this->messageManager->addError(
                    __('%1 Products are not added to cart.<a href="#" id="errors-lists">Click here to view details </a>', count($allErrors))
                );
                $this->_checkoutSession->setQopErrors($allErrors);
            } elseif (!empty($allErrors[0])) {
                $this->messageManager->addError($allErrors[0]);
            }
        }

        if (isset($configureProducts['products']) && !empty($configureProducts['products'])) {
            $helper->addConfigureListProducts($configureProducts['products']);
            $helper->addConfigureListQtys($configureProducts['qty']);
            $this->messageManager->addErrorMessage('One or more products require configuration before they can be added to the Cart. See list below');
            $this->getResponse()->setRedirect($this->_url->getUrl('quickorderpad/form/results', array('csv' => 1)));
        } else {
            $this->_goBack();
        }
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
