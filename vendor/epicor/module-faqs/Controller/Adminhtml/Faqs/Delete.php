<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Adminhtml\Faqs;

class Delete extends \Epicor\Faqs\Controller\Adminhtml\Faqs
{

    /**
     * @var \Epicor\Faqs\Model\FaqsFactory
     */
    protected $faqsFaqsFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Faqs\Model\FaqsFactory $faqsFaqsFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->faqsFaqsFactory = $faqsFaqsFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Delete action
     */
    public function execute()
    {
        // check if we know what should be deleted
        $itemId = $this->getRequest()->getParam('id');
        if ($itemId) {
            try {
                // init model and delete
                /** @var $model Epicor_Faqs_Model_Item */
                $model = $this->faqsFaqsFactory->create();
                $model->load($itemId);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('Unable to find a F.A.Q. item.'));
                }
                $model->delete();

                // display success message
                $this->messageManager->addSuccessMessage((
                    __('The F.A.Q. has been deleted.')
                ));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('An error occurred while deleting the F.A.Q. item.')
                );
            }
        }

        // go to grid
        $this->_redirect('*/*/');
    }

    }
