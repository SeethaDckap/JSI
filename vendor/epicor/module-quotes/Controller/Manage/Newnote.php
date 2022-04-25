<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;
//use Magento\Framework\Controller\ResultFactory; 

class Newnote extends \Epicor\Quotes\Controller\Manage
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
       \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
    ) {
        $this->customerSession = $customerSession;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
         parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


public function execute()
    {
        $successMsg = __('New comment has been added to the quote.');
        $errorMsg = __('Error has occurred while adding the new comment to this quote.');

        if (!$this->customerSession->authenticate($this)) {
            return;
        }

        try {

            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */
            $customer = $this->customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            if (!$quote->canBeAccessedByCustomer($customer)) { 
                $errorMsg .= __(': You do not have permission to access this quote');
                throw new \Exception('Invalid customer');
            }

            $note = $this->getRequest()->get('note');
            if (!$note) {
                $errorMsg = __('Comment was empty. Please try again.');
                throw new \Exception('No Note found');
            }

            $quote->addNote($note, null, true, false, true);

            $quote->save();

            $this->messageManager->addSuccess($successMsg);
            $error = false;
        } catch (\Exception $e) {
            $this->messageManager->addError($errorMsg);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->messageManager->addError($errorMsg);
        }
      
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
}
