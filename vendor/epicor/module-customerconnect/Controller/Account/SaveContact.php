<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Account;

class SaveContact extends \Epicor\Customerconnect\Controller\Account
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

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
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    )
    {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }

    public function execute()
    {
        $helper = $this->customerconnectHelper;

        $data = $this->getRequest()->getPost();

        $error = false;

        $customer = $this->customerCustomerFactory->create();
        $customer->setWebsiteId($this->storeManager->getDefaultStoreView()->getWebsiteId());
        if ($data) {

            $form_data = json_decode($data['json_form_data'], true);
            $old_form_data = json_decode($form_data['old_data'], true);


            unset($form_data['old_data']);
            $customerExists = false;
            $form_data['login_id'] = 'false';
            if (isset($form_data['web_enabled'])) {

                $form_data['login_id'] = 'true';
                $customer->loadByEmail($form_data['email_address']);
                if ($customer->getId()) {
                    if ($form_data['ecc_master_shopper'] == 'y') {
                        $customer->setEccMasterShopper(1);
                    } else {
                        $customer->setEccMasterShopper(0);
                    }
                    $customer->getResource()->saveAttribute($customer, 'ecc_master_shopper');
                }
                unset($form_data['web_enabled']);

                if (!isset($old_form_data['email_address']) || $old_form_data['email_address'] != $form_data['email_address']) {
                   $customer->loadByEmail($form_data['email_address']);
                    if ($customer && !$customer->isObjectNew()) {
                        $customerExists = true;
                    }
                }
            }
            // add this otherwise the difference check will always be true and always send a message
            $form_data['contact_code'] = $old_form_data['contact_code'];

            $access_groups = null;
            if (isset($form_data['access_groups'])) {
                $accessHelper = $this->commonAccessHelper;
                if ($accessHelper->customerHasAccess('Epicor_Customerconnect', 'Account', 'index', 'manage_permissions', 'view')) {
                    $this->updateContactAccessGroups($form_data['contact_code'], $form_data['access_groups']);
                }
                unset($form_data['access_groups']);
            }

            if ($customerExists) {
                $this->messageManager->addErrorMessage(__('Contact error: Email address already exists'));
                $error = true;
            } else if($old_form_data['ecc_web_enabled'] == 1 && $form_data['contact_code']== null && $form_data['login_id']== "false"){
                $formattedMasterShopper = ($form_data['ecc_master_shopper'] == 'y') ? '1' : '0';
                $customer->loadByEmail($form_data['email_address']);
                $customerRepository = $this->customerRepository->getById($customer->getId());
                $customerRepository->setFirstname($form_data['firstname']);
                $customerRepository->setLastname($form_data['lastname']);
                $customerRepository->setEmail($form_data['email_address']);
                $customerRepository->setCustomAttribute('ecc_function', $form_data['function']);
                $customerRepository->setCustomAttribute('ecc_telephone_number', $form_data['telephone_number']);
                $customerRepository->setCustomAttribute('ecc_fax_number', $form_data['fax_number']);
//                $customerRepository->setCustomAttribute('ecc_erpaccount_id', $customer->getEccErpaccountId());
//                $customerRepository->setCustomAttribute('ecc_erp_account_type', 'customer');
//                $customerRepository->setCustomAttribute('ecc_contact_code', $customer->getEccContactCode());
                $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                $extensionAttributes->setEccMultiErpId($customer->getEccErpaccountId());
                $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode());
                $extensionAttributes->setEccMultiErpType('customer');
                $customerRepository->setExtensionAttributes($extensionAttributes);
                $customerRepository->setCustomAttribute('ecc_master_shopper', $formattedMasterShopper);
                $customerRepository->setCustomAttribute('ecc_cuco_pending', $customer->getEccCucoPending());
                $customerRepository->setCustomAttribute('ecc_erp_login_id', $customer->getEccErpLoginId());

                $this->customerRepository->save($customerRepository); 
                $this->messageManager->addSuccess(__('Contact updated successfully'));
                $resultData = json_encode(array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success'));              
            } else if ($old_form_data != $form_data) {
                
                $message = $this->customerconnectMessageRequestCuau;

                if (empty($old_form_data)) {
                    $action = 'A';
                } else {
                    if ($old_form_data['source'] === $helper::SYNC_OPTION_ONLY_ECC) {
                        $action = 'A';
                    } else {
                        $action = 'U';
                    }
                }
                $message->addContact($action, $form_data, $old_form_data);

                if ($action == 'U') {
                    $this->_successMsg = __('Contact updated successfully');
                    $this->_errorMsg = __('Failed to update Contact');
                } else {
                    $this->_successMsg = __('Contact added successfully');
                    $this->_errorMsg = __('Failed to add Contact');
                }
                $resultData = $this->sendUpdate($message);
            } else {
                $this->messageManager->addNoticeMessage(__('No changes made to Contact'));
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //echo json_encode(array('redirect' => Mage::getUrl('customerconnect/account/'), 'type' => 'success'));
            $resultData = json_encode(array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success'));
            //M1 > M2 Translation End
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($resultData);

        return $result;
    }

}
