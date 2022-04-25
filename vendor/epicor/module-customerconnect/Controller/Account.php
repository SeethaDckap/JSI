<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller;


/**
 * Customer Account controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Account extends \Epicor\Customerconnect\Controller\Generic
{

    protected $_successMsg;
    protected $_errorMsg;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\CustomerFactory
     */
    protected $commonAccessGroupCustomerFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->commHelper = $commHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->generic = $generic;
        $this->cache = $cache;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Updates a contact with the provided access groups
     *
     * @param string $contact
     * @param array $groupIds
     */
    protected function updateContactAccessGroups($contactCode, $groupIds)
    {

        // load the customer by contact code & ERP account ID
        $customerSession = $this->customerSession;
        $commHelper = $this->commHelper;
        $erpAccount = $commHelper->getErpAccountInfo();
        $erpAccountId = $erpAccount->getId();

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
        $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccountId);
        $customer = $collection->getFirstItem();

        if ($customer && !$customer->isObjectNew() && $customer->getId() != $customerSession->getCustomer()->getId()) {
            $this->commonAccessGroupCustomerFactory->create()->updateCustomerAccessGroups($customer->getId(), $groupIds);
        }
    }
/**
     * Index action
     *
     * @var $message \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected function sendUpdate($message)
    {
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $messageTypeCheck = $message->getHelper()->getMessageType('CUAU');
        $error = false;

        if ($message->isActive() && $messageTypeCheck) {

            //M1 > M2 Translation Begin (Rule p2-6.4)
            /*$message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));*/
            $message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            //M1 > M2 Translation End


            if ($message->sendMessage() && $message->getStatusCode() == '200') {
                $this->messageManager->addSuccessMessage($this->_successMsg);
            } else {
                $this->messageManager->addErrorMessage($this->_errorMsg . ': ' . $message->getStatusDescription());
                $error = true;
            }
        } else {
            $this->messageManager->addErrorMessage(__('Account update not available'));
            $error = true;
        }

        //M1 > M2 Translation Begin (Rule p2-4)
        return array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success', 'message' => $message, 'error' => $error);
        //M1 > M2 Translation End
    }

}
