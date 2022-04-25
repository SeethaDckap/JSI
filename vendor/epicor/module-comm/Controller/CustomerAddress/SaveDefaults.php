<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\CustomerAddress;

use Epicor\Comm\Controller\CustomerAddress;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Exception;

/**
 * Class SaveDefaults
 */
class SaveDefaults extends CustomerAddress
{


    /**
     * Process address form save
     *
     * @return Redirect
     */
    public function execute()
    {
        $redirectUrl = null;
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        if (!$this->getRequest()->isPost()) {
            $this->customerSession->setAddressFormData($this->getRequest()->getPostValue());
            return $this->resultRedirectFactory->create()->setUrl($this->getRedirectUrl());
        }

        try {
            $address = $this->extractAddress();
            $address->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
            $this->addressRepository->save($address);
            $this->messageManager->addSuccessMessage(__('You saved the address.'));
            $url = $this->urlBuilder->getUrl('customer/address', ['_secure' => true]);
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t save the address.'));
        }

        $url = $redirectUrl;
        if (!$redirectUrl) {
            $this->customerSession->setAddressFormData($this->getRequest()->getPostValue());
            $url = $this->getRedirectUrl();
        }

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));

    }//end execute()


    /**
     * Retrieve existing address data
     *
     * @return mixed
     *
     * @throws Exception Throw exception.
     */
    private function extractAddress()
    {
        $existingAddress = false;
        if ($addressId = $this->getRequest()->getParam('id')) {
            $existingAddress = $this->addressRepository->getById($addressId);
            if ($existingAddress->getCustomerId() !== $this->customerSession->getCustomerId()) {
                throw new Exception();
            }
        }

        return $existingAddress;

    }//end extractAddress()


    /**
     * Get redirect url.
     *
     * @return string
     */
    private function getRedirectUrl()
    {
        return $this->urlBuilder->getUrl('customer/address/edit',
            [
                '_secure' => true,
                'id'      => $this->getRequest()->getParam('id'),
            ]
        );

    }//end getRedirectUrl()


}//end class
