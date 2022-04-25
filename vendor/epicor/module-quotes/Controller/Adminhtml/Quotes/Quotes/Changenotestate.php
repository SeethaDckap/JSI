<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Changenotestate extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Epicor\Quotes\Model\Quote\NoteFactory
     */
    protected $quotesQuoteNoteFactory;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;
      /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /*
    public function __construct(
        \Epicor\Quotes\Model\Quote\NoteFactory $quotesQuoteNoteFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->quotesQuoteNoteFactory = $quotesQuoteNoteFactory;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->registry = $registry;
        $this->backendSession = $backendSession;
    }
     */
      public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Quotes\Model\Quote\NoteFactory $quotesQuoteNoteFactory      
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->backendHelper = $context->getHelper();
        $this->registry = $context->getRegistry();
        $this->_resultPageFactory = $context->getResultPageFactory();
        $this->quotesQuoteNoteFactory = $quotesQuoteNoteFactory;
        parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
    }
    
    public function execute()
    {

        $successMsg = __('Comment has been saved');
        $errorMsg = __('Error occurred while trying to save the comment');
        $error = true;
        $message = $errorMsg;
        $html = '';
        try {
            $note = $this->quotesQuoteNoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $note Epicor_Quotes_Model_Quote_Note */

            switch ($this->getRequest()->get('state')) {
                case \Epicor\Quotes\Model\Quote\Note::STATE_PUBLISH_NOW:
                    $note->setIsVisible(true);
                    $note->setIsPrivate(false);
                    $note->setSendEmail(true);
                    //$note->setCreatedAt(time());
                    break;
                case \Epicor\Quotes\Model\Quote\Note::STATE_PRIVATE:
                    $note->setIsVisible(false);
                    $note->setIsPrivate(true);
                    break;
            }
            $note->save();
            $quote = $this->quotesQuoteFactory->create()->load($note->getQuoteId());
            /* @var $quote Epicor_Quotes_Model_Quote */
            $quote->getCustomer(true);
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
            
            $this->messageManager->addSuccess($successMsg);
            $error = false;
            $message = $successMsg;
        } catch (\Exception $e) {
                $this->messageManager->addError($errorMsg);
        } catch (Mage_Exception $e) {
                $this->messageManager->addError($errorMsg);
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
