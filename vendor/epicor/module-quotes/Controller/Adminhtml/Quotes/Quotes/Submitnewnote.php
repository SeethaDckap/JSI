<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Submitnewnote extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
     /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    
      public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->backendHelper = $context->getHelper();
        $this->registry = $context->getRegistry();
        $this->_resultPageFactory = $context->getResultPageFactory();
        parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
    }
    /*
    public function __construct(
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->registry = $registry;
    }
     */
    
    public function execute()
    {

        $successMsg = __('Comment has been saved');
        $errorMsg = __('Error occurred while trying to save the comment');
        $error = true;

        $html = '';
        try {
            $noteText = $this->getRequest()->get('note');

            if (!$noteText)
                throw new \Exception(__('Comment textarea empty'));

            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */
            $quote->getCustomer(true);

            $adminId = $this->backendAuthSession->getUser()->getId();
            $email = ($this->getRequest()->get('state') == \Epicor\Quotes\Model\Quote\Note::STATE_PUBLISH_NOW);
            $visible = ($this->getRequest()->get('state') == \Epicor\Quotes\Model\Quote\Note::STATE_PUBLISH_NOW);
            $private = ($this->getRequest()->get('state') == \Epicor\Quotes\Model\Quote\Note::STATE_PRIVATE);

            $quote->addNote($noteText, $adminId, $visible, $private, $email);
            $quote->save();
            $quote->refreshNotes();

            $this->registry->register('quote', $quote);
            /*
            $block = $this->getLayout()->createBlock('quotes/adminhtml_quotes_edit_commenthistory');
            $block->setTemplate('quotes/edit/commenthistory.phtml');
            $html = $block->toHtml();
            */
            $resultPage = $this->_resultPageFactory->create();
            $html  = $resultPage->getLayout()
                ->createBlock('Epicor\Quotes\Block\Adminhtml\Quotes\Edit\Commenthistory')
                ->setTemplate('Epicor_Quotes::quotes/edit/commenthistory.phtml')
                ->toHtml();
          
            $error = false;
            $message = $successMsg;
        } catch (\Exception $e) {
            $message = $errorMsg;
        } catch (Mage_Exception $e) {
            $message = $errorMsg;
        }
        $this->getResponse()->setBody(
            json_encode(
                array(
                    'replace' => 'quote-notes',
                    'error' => $error,
                    'message' => $message,
                    'html' => $html
                )
            )
        );
    }

}
