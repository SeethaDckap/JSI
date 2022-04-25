<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Adminhtml\Faqs;

class Save extends \Epicor\Faqs\Controller\Adminhtml\Faqs
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
     * Save action
     */
    public function execute()
    {
        $redirectPath = '*/*';
        $redirectParams = array();

        // check if data sent
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $data = $this->_filterPostData($data);
            // init model and set data
            /* @var $model Epicor_Faqs_Model_Item */
            $model = $this->faqsFaqsFactory->create();

            // if faqs item exists, try to load it
            $faqsId = $this->getRequest()->getParam('id');
            if ($faqsId) {
                $model->load($faqsId);
            }

            $model->addData($data);

            try {
                $hasError = false;
                // save the data
                $model->save();

                // display success message
                $this->messageManager->addSuccessMessage(
                    __('The F.A.Q. has been saved.')
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath = '*/*/edit';
                    $redirectParams = array('id' => $model->getId());
                }
            } catch (LocalizedException  $e) {
                $hasError = true;
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e, __('An error occurred while saving the F.A.Q. item.')
                );
            }
            $this->_registry->register('epicor_faq_data', $data);
            if ($hasError) {
                $this->_session->setFormData($data);
                $redirectPath = '*/*/edit';
                $redirectParams = array('id' => $this->getRequest()->getParam('id'));
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }

    }
