<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Adminhtml\Faqs;

class Edit extends \Epicor\Faqs\Controller\Adminhtml\Faqs
{

    /**
     * @var \Epicor\Faqs\Model\FaqsFactory
     */
    protected $faqsFaqsFactory;
    /**
     * @var Escaper
     */
    protected $escaper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Escaper $escaper,
        \Epicor\Faqs\Model\FaqsFactory $faqsFaqsFactory)
    {
        $this->faqsFaqsFactory = $faqsFaqsFactory;
        $this->escaper = $escaper;
        parent::__construct($context, $backendAuthSession);
    }
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Common::faqs')
            ->addBreadcrumb(__('F.A.Q.'), __('F.A.Q.'))
            ->addBreadcrumb(__('Manage F.A.Q.'), __('Manage F.A.Q.'));
        return $resultPage;
    }
    /**
     * Edit Faqs item
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        /** @var Faqs $model */
        $model = $this->_objectManager->create('Epicor\Faqs\Model\Faqs');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('F.A.Q. item does not exist.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }

            $this->_registry->register('epicor_faq_data', $model->getData());
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Item') : __('New Item'),
            $id ? __('Edit Item') : __('New Item')
        );
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf("Edit Faqs Item %s", $this->escaper->escapeHtml($model->getQuestion()))) : __('New Faqs Item'));

        return $resultPage;
    }

    }
