<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Country;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Country
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CountryFactory
     */
    protected $commErpMappingCountryFactory;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Erp\Mapping\CountryFactory $commErpMappingCountryFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commErpMappingCountryFactory = $commErpMappingCountryFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commErpMappingCountryFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The country has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the country to delete.'));
        $this->_redirect('*/*/');
    }

    }
