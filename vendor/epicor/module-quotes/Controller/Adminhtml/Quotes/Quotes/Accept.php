<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Accept extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

     public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->backendHelper = $context->getHelper();
        $this->registry = $context->getRegistry();
        parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
    }
    
    public function execute()
    {
        if (!$this->registry->registry('gqr-accept')) {
            $this->registry->register('gqr-accept', true);  // this stops the gqr being sent within the savePost) 
        }
        $successMsg = __('Quote has been accepted');
        $errorMsg = __('Error occurred while trying to accepted the quote');
        $error = true;
        try {
            if (!$this->savePost())
                throw new \Exception('Failed to Save Quote Data');
            
                $this->registry->unregister('gqr-accept');    // allows the gqr to be sent on next save()
                $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
                /* @var $quote Epicor_Quotes_Model_Quote */
                $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_AWAITING_ACCEPTANCE);
                $quote->save();
                $time = time();
                
                foreach ($quote->getNonVisibleNotes() as $note) {
                    $note->setIsVisible(true);
                    $note->setCreatedAt($time);
                    $note->save();
                    $time++;
                }

            $this->messageManager->addSuccess($successMsg);
            $error = false;
        } catch (\Exception $e) {
               $this->messageManager->addError($errorMsg);
        } catch (Mage_Exception $e) {
                   $this->messageManager->addError($errorMsg);
        }

        $this->getResponse()->setBody(
            json_encode(
                array(
                    'redirectUrl' => $this->backendHelper->getUrl('*/*/'),
                    'error' => $error,
                    'errorMsg' => $errorMsg
                )
            )
        );
    }
}
