<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group;

use Epicor\Common\Block\Generic\Listing\Grid as ListingGrid;
use Epicor\OrderApproval\Model\GroupsRepository;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory as GroupCollectionFactory;
use Epicor\OrderApproval\Model\Groups as ApprovalGroups;

Abstract class AbstractGroupBlock extends ListingGrid
{
    /**
     * @var GroupsRepository
     */
    protected $groupsRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $group;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var ApprovalGroups
     */
    protected $groups;

    /**
     * AbstractGroupBlock constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
     * @param GroupsRepository $groupsRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param ApprovalGroups $groups
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        GroupsRepository $groupsRepository,
        \Magento\Customer\Model\Session $customerSession,
        GroupCollectionFactory $groupCollectionFactory,
        ApprovalGroups $groups,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );
        $this->context = $context;
        $this->groupsRepository = $groupsRepository;
        $this->customerSession = $customerSession;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->groups = $groups;
    }

    /**
     * @return mixed
     */
    protected function getGroupId()
    {
        if ($id = $this->context->getRequest()->getParam('id')) {
            return $id;
        }
    }

    /**
     * @return array
     */
    protected function getAllGroups()
    {
        $groups = $this->groupCollectionFactory->create();
        $groups->addFieldToSelect('group_id');
        return array_values($groups->getAllIds());
    }

    /**
     * @return array
     */
    protected function getDisabledFields()
    {
        return [];
    }
}