<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Brandpost extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
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
        $this->listsListModelBrandFactory = $listsListModelBrandFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Brands ajax post
     *
     * @return void
     */
    public function execute()
    {
        $response = array();
        $response['type'] = 'success-msg';
        $response['message'] = __('Brand was successfully saved.');

        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('brand_id');
            $model = $this->listsListModelBrandFactory->create();
            /* @var $model Epicor_Lists_Model_ListModel_Brand */

            try {
                if ($id) {
                    $model->load($id);
                }

                $model->setListId($this->getRequest()->getParam('list_id'));
                $model->setCompany($this->getRequest()->getParam('company'));
                $model->setSite($this->getRequest()->getParam('site'));
                $model->setWarehouse($this->getRequest()->getParam('warehouse'));
                $model->setGroup($this->getRequest()->getParam('group'));

                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Brand'));
                }
            } catch (\Exception $e) {
                $response['type'] = 'error-msg';
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['type'] = 'error-msg';
            $response['message'] = __('No data found to save');
        }

        //M1 > M2 Translation Begin (Rule p2-7)
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($response));
        //M1 > M2 Translation End
    }

    }
