<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Language;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Language
{

    /**
     * @var \Epicor\Common\Model\Erp\Mapping\LanguageFactory
     */
    protected $commonErpMappingLanguageFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Common\Model\Erp\Mapping\LanguageFactory $commonErpMappingLanguageFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commonErpMappingLanguageFactory = $commonErpMappingLanguageFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commonErpMappingLanguageFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The language has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addErrorMessager(__('Unable to find the language to delete.'));
        $this->_redirect('*/*/');
    }

    }
