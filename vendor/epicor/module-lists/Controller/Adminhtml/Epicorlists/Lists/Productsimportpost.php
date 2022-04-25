<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

use Magento\Framework\Controller\ResultFactory;

class Productsimportpost extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{
    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,        
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->listsHelper = $listsHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Ajax Import Products Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!in_array($_FILES['import']['type'],
            \Epicor\Comm\Helper\Data::CSV_APPLIED_FORMAT)
        ) {
            $errors = 'Wrong File Type. Only CSV files are allowed.';

            return $resultJson->setData(['errors' => $errors]);
        }

        $helper = $this->listsHelper;
        /* @var $helper \Epicor\Lists\Helper\Data */

        $list = $this->loadEntity();
        /* @var $list \Epicor\Lists\Model\ListModel */

        $deleteAllProducts = $this->getRequest()->getParam('deleteAllProducts', 0);
        if (!$list->isObjectNew() && $deleteAllProducts) {
            $list->removeAllProducts();
        }

        $errors = array();
        if (!$list->isObjectNew() && !empty($_FILES['import']['tmp_name'])) {
            $errors = $helper->importCsvProducts($list, $_FILES['import']['tmp_name']);
            $list->save();
        }

        $productIds = array_keys($list->getProducts());

        return $resultJson->setData(['products' => $productIds, 'errors' => $errors]);

    }//end execute()

}
