<?php
/**
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Plugin\Account;

use Magento\Customer\Model\Session;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractCuau
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Session $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Registry $registry
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_request = $request;
        $this->session = $customerSession;
        $this->customer = $customer;
        $this->_registry = $registry;
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function preparedata($customer_id, $postData)
    {
        $currentCustomerDataObject = $this->getCustomerDataObject($customer_id);
        $oldContact = [];
        $newContact = [];
        if ($currentCustomerDataObject->getEccContactCode() && $this->_request->isPost()) {
            $oldContact['contact_code'] = $currentCustomerDataObject->getEccContactCode();
            $name = $currentCustomerDataObject->getFirstname();
            if ($currentCustomerDataObject->getMiddlename()) {
                $name = $name . ' ' . $currentCustomerDataObject->getMiddlename();
            }
            $name = $name . ' ' . $currentCustomerDataObject->getLastname();
            $oldContact['name'] = $name;
            $oldContact['function'] = $currentCustomerDataObject->getEccFunction();
            $oldContact['telephone_number'] = ($currentCustomerDataObject->getEccTelephoneNumber() === null) ? "" :
                $currentCustomerDataObject->getEccTelephoneNumber();
            $oldContact['fax_number'] = ($currentCustomerDataObject->getEccFaxNumber() === null) ? "" :
                $currentCustomerDataObject->getEccFaxNumber();

            $oldContact['email_address'] = $currentCustomerDataObject->getEmail();
            $oldContact['login_id'] = $currentCustomerDataObject->getEccErpLoginId();
            $newContact = $oldContact;
            if ($this->hasErpAccountChanged($currentCustomerDataObject, $postData)) {
                $newContact['ecc_erpaccount_changed'] = true;
            }
            $newname = $postData['firstname'];
            if (isset($postData['middlename']) && $postData['middlename']) {
                $newname = $newname . ' ' . $postData['middlename'];
            }
            $newname = $newname . ' ' . $postData['lastname'];
            $newContact['name'] = $newname;
            $newContact['login_id'] = 'true';
            if (isset($postData['email']) && $postData['email']) {
                $newContact['email_address'] = $postData['email'];
            }
            if (isset($postData['ecc_function'])) $newContact['function'] = $postData['ecc_function'];
            if (isset($postData['ecc_telephone_number']))
                $newContact['telephone_number'] = $postData['ecc_telephone_number'];
            if (isset($postData['ecc_fax_number'])) $newContact['fax_number'] = $postData['ecc_fax_number'];
            $this->_registry->register('newContact', $newContact);
            $this->_registry->register('oldContact', $oldContact);
        }
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customer->load($customerId);
    }
    /**
     * check if erp account has changed, of so return true, else false
     * @param \Magento\Customer\Model\Customer $currentCustomerDataObject
     * @param array $postData
     * @return bool
     */
    private function hasErpAccountChanged(\Magento\Customer\Model\Customer $currentCustomerDataObject,
                                            array $postData)
    {
        if (isset($postData['ecc_erpaccount_id']) &&
            ($postData['ecc_erpaccount_id'] != $currentCustomerDataObject->getEccErpaccountId())
        ) {
            return true;
        } else {
            return false;
        }
    }

}
