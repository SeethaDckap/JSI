<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Branddelete extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ListModel\BrandFactory
     */
    protected $listsListModelBrandFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Model\ListModel\BrandFactory $listsListModelBrandFactory
    ) {
        parent::__construct($context, $backendAuthSession);
        $this->listsListModelBrandFactory = $listsListModelBrandFactory;
    }
    /**
     * Brands ajax delete
     *
     * @return void
     */
    public function execute()
    {
        $response = array();
        $response['type'] = 'success-msg';
        $response['message'] = __('Brand was successfully deleted.');

        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->listsListModelBrandFactory->create();
            /* @var $model Epicor_Lists_Model_ListModel_Brand */

            try {
                $model->load($id);

                if (!$id || !$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('No data found to delete'));
                }

                $model->delete();
            } catch (\Exception $e) {
                $response['type'] = 'error-msg';
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['type'] = 'error-msg';
            $response['message'] = __('No data found to delete');
        }

        //M1 > M2 Translation Begin (Rule p2-7)
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($response));
        //M1 > M2 Translation End
    }

    }
