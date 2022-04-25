<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\AccessRight;

class ApplyRoles
{
    /**
     * @var array allowedResources
     */
    protected $allowedResources = null;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;


    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $cmsCollectionFactory;

    /**
     * Constructor function.
     *
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmscollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmscollectionFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->customerSession = $customerSession;
        $this->cmscollectionFactory = $cmscollectionFactory;
    }

    /**
     * After isAccessRigtsEnabled Plugin.
     *
     * @param Epicor\AccessRight\Model\ApplyRoles $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterIsAccessRigtsEnabled(\Epicor\AccessRight\Model\ApplyRoles $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $this->customerSession->setNoRuleApplied(false);
            $return = true;
        }
        return $return;
    }

    /**
     * After isAccessRigtsEnabled Plugin.
     *
     * @param Epicor\AccessRight\Model\ApplyRoles $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterFrontendApplyRole(\Epicor\AccessRight\Model\ApplyRoles $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout() && !$this->customerSession->getPunchoutResource()) {
            $allowed = $this->getAllowedResource();
            $this->customerSession->setAllowedResource($allowed);
            $this->customerSession->setPunchoutResource(true);
        }
        return $return;
    }

    /**
     * Get a list of available resource using user role id
     *
     * @return string[]
     */
    public function getAllowedResource()
    {
        if ($this->allowedResources === null) {
            $this->allowedResources = ['Magento_Frontend::all', 'Epicor_Checkout::checkout', 'Epicor_Checkout::checkout_checkout', 'Epicor_Checkout::checkout_checkout_can_checkout', 'Epicor_Checkout::checkout_quick_order_pad', 'Epicor_Checkout::catalog_search', 'Epicor_Checkout::catalog_advance_search', 'Epicor_Checkout::catalog_quick_add'];
            $collection = $this->cmscollectionFactory->create();
            if ($collection->getSize()) {
                foreach ($collection as $cms) {
                    $this->allowedResources[] = 'Epicor_CMS::cms_' . $cms->getId();
                }
            }
        }
        return $this->allowedResources;
    }

}
