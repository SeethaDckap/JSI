<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations;

class Erpaccountsgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations
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

        $erpaccounts = $this->getRequest()->getParam('erpaccounts');
        $this->_registry->register('location', $location);
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('erpaccounts_grid')
            ->setSelected($erpaccounts);
        return $resultLayout;
    }

    }
