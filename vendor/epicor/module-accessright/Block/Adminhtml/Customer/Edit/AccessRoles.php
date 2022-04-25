<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Customer\Edit;

/**
 * Customer account form block
 */
class AccessRoles extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRightsOptions $accessRightOptions,
        \Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRoles $accessRoles,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->accessRightOptions =  $accessRightOptions;
        $this->accessRoles =  $accessRoles;
        $this->customerRepository =  $customerRepository;
        parent::__construct($context, $data);
    }

    public function getAccessRights() {
        return $this->accessRightOptions;
    }

    public function getAccessRoles() {
        return $this->accessRoles;
    }

    public function getEccAccessRights() {
        $getEccAccessRights = 2;
        if ($this->accessRoles->getCustomerId()) {
            $customerRepository = $this->customerRepository->getById($this->accessRoles->getCustomerId()); 
            if ($customerRepository->getCustomAttribute('ecc_access_rights')) {
                $getEccAccessRights = $customerRepository->getCustomAttribute('ecc_access_rights')->getValue();
            }
        }
        return $getEccAccessRights;
    }

}
