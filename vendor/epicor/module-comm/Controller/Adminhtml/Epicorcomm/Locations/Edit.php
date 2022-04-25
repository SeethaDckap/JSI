<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations;

class Edit extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations
{

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commLocationFactory = $commLocationFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $location = $this->commLocationFactory->create()->load($this->getRequest()->get('id'));
        /* @var $location Epicor_Comm_Model_Location */

        $this->_registry->register('location', $location);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::locations');
        $resultPage->getConfig()->getTitle()
            ->prepend(__('location:'.$location->getCode()));

        return $resultPage;
    }

    }
