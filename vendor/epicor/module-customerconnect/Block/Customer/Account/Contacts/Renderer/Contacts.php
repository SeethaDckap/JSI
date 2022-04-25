<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer;


class Contacts extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();


    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

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

    //   protected $updateList = Array();
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\AccessRight\Helper\AccessRoles $eccAccessRoles,
        array $data = []
    )
    {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->eccAccessRoles = $eccAccessRoles;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        
        $customer = $this->customerCustomerFactory->create();
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
        $customer->loadByEmail($row->getEmailAddress());
        $customer->getId() ? $is_ecc_customer = 1 : $is_ecc_customer = 0;
        $erpAccountId = ($customer->getEccErpaccountId()) ? $customer->getEccErpaccountId() : $customer->getEccSupplierErpaccountId();
        $webEnabled = $customer->getEccWebEnabled();
        $hidePrices = $customer->getEccHidePrice();
        $customer->getId() ? $accessroleCusId = $customer->getId() : $accessroleCusId = $this->customerSession->getCustomerId();
        $jsonArray = json_encode(array(
            'contact_code' => $row->getContactCode(),
            'name' => $row->getName(),
            'firstname' => $row->getFirstname(),
            'lastname' => $row->getLastname(),
            'function' => $row->getFunction(),
            'telephone_number' => $row->getTelephoneNumber(),
            'fax_number' => $row->getFaxNumber(),
            'email_address' => $row->getEmailAddress(),
            'login_id' => $row->getLoginId(),
            'source' => $row->getSource(),
            'master_shopper' => $row->getMasterShopper(),
            'ecc_hide_prices' => $hidePrices,
            'ecc_access_rights' => $customer->getEccAccessRights(),
            'ecc_access_roles' => $this->eccAccessRoles->getAccessRoles($accessroleCusId, $erpAccountId),
            'is_ecc_customer' => $is_ecc_customer,
            'login_mode_type'=>$row->getLoginModeType(),
            'is_toggle_allowed'=>$row->getIsToggleAllowed(),
            'ecc_web_enabled'=>$webEnabled
        ));

        $html = '<input type="hidden" class="details" name="details" value="' . htmlspecialchars($jsonArray) . '"/> ';
        $html .= $row->getName();

        if ($this->registry->registry('manage_permissions')) {
            $customerSession = $this->customerSession;
            $commHelper = $this->commHelper;
            $erpAccount = $commHelper->getErpAccountInfo();
            $erpAccountId = $erpAccount->getId();
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $contractCode = $row->getContactCode();
            if($contractCode) {
                $collection->addAttributeToFilter('ecc_contact_code', $row->getContactCode());
            }
            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccountId);
            $customer = $collection->getFirstItem();

            $groups = array();

            if ($customer && !$customer->isObjectNew()) {
                $groupsArray = $this->commonAccessGroupCustomerFactory->create()->getCustomerAccessGroups($customer->getId());
                foreach ($groupsArray as $group) {
                    $groups[] = $group->getGroupId();
                }
            }

            $html .= '<input type="hidden" name="groups" value="' . implode(',', $groups) . '"/> ';
        }

        return $html;
    }

}
