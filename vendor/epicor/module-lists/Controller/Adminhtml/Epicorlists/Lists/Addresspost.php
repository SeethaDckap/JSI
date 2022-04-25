<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Addresspost extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ListModel\AddressFactory
     */
    protected $listsListModelAddressFactory;

    public function __construct(
        \Epicor\Lists\Model\ListModel\AddressFactory $listsListModelAddressFactory
    ) {
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
    }
    /**
     * Address ajax post
     *
     * @return void
     */
    public function execute()
    {
        $response = array();
        $response['type'] = 'success-msg';
        $response['message'] = __('Address was successfully saved.');
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('address_id');
            $model = $this->listsListModelAddressFactory->create();
            /* @var $model \Epicor\Lists\Model\ListModel\Address */

            try {
                if ($id) {
                    $model->load($id);
                }

                $model->setListId($this->getRequest()->getParam('list_id'));
                $model->setAddressCode($this->getRequest()->getParam('address_code'));
                $model->setName($this->getRequest()->getParam('address_name'));
                //M1 > M2 Translation Begin (Rule 9)
                /*$model->setAddress1($this->getRequest()->getParam('address1'));
                $model->setAddress2($this->getRequest()->getParam('address2'));
                $model->setAddress3($this->getRequest()->getParam('address3'));*/
                $model->setData('address1', $this->getRequest()->getParam('address1'));
                $model->setData('address2', $this->getRequest()->getParam('address2'));
                $model->setData('address3', $this->getRequest()->getParam('address3'));
                //M1 > M2 Translation End
                $model->setCity($this->getRequest()->getParam('city'));
                $model->setCounty($this->getRequest()->getParam('county'));
                $model->setCountry($this->getRequest()->getParam('country'));
                $model->setPostcode($this->getRequest()->getParam('postcode'));
                $model->setTelephoneNumber($this->getRequest()->getParam('telephone_number'));
                $model->setMobileNumber($this->getRequest()->getParam('mobile_number'));
                $model->setFaxNumber($this->getRequest()->getParam('fax_number'));
                $model->setEmailAddress($this->getRequest()->getParam('email_address'));

                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Address'));
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
