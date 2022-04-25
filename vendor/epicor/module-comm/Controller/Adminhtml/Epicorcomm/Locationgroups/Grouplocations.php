<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locationgroups;

class Grouplocations extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Epicor\Comm\Model\Location\GroupsFactory
     */
    protected $commLocationGroupsFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Location\GroupsFactory $commLocationGroupsFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commLocationGroupsFactory = $commLocationGroupsFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $group = $this->commLocationGroupsFactory->create()->load($this->getRequest()->get('id'));
        /* @var $location \Epicor\Comm\Model\Location\Groups */
        $this->_registry->register('group', $group);
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('grouplocations_grid')
            ->setSelected($this->getRequest()->getPost('grouplocations', null));

        return $resultLayout;
    }

}