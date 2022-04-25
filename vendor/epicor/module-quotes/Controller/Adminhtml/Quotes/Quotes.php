<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes;


abstract class Quotes extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_aclId = 'Epicor_Quotes::sales_quotes';

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendSession;
    
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
        //  \Magento\Framework\Registry $registry  
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->backendSession = $backendAuthSession;
        parent::__construct($context, $backendAuthSession);
    }
    
    protected function _initPage()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Quotes::sales_quotes');
        
        return $resultPage;
    }
    
    protected function savePost()
    {
        $saved = false;
        if ($data = $this->getRequest()->getPost()) {
            try {

                $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
                /* @var $quote Epicor_Quotes_Model_Quote */
                $prices = json_decode($data['prices'], true);
                $qtys = json_decode($data['qtys'], true);
                $noteText = $this->getRequest()->getPost('note');
                $quote->setSendAdminComments($this->getRequest()->getPost('send_comments') == 'true');
                $quote->setSendAdminReminders($this->getRequest()->getPost('send_reminders') == 'true');
                $quote->setSendAdminUpdates($this->getRequest()->getPost('send_updates') == 'true');
                $quote->setIsGlobal($this->getRequest()->getPost('is_global') == 'true');

                if (!empty($noteText)) {

                    $adminId = $this->backendAuthSession->getUser()->getId();
                    $visible = ($this->getRequest()->get('state') == \Epicor\Quotes\Model\Quote\Note::STATE_PUBLISH_NOW);
                    $private = ($this->getRequest()->get('state') == \Epicor\Quotes\Model\Quote\Note::STATE_PRIVATE);
                    $quote->addNote($noteText, $adminId, $visible, $private, false);
                }

                foreach ($quote->getProducts() as $product) {
                    /* @var $product Epicor_Quotes_Model_Quote_Product */
                    $quote->setProductNewPrice($prices[$product->getId()], $product->getId());
                    $quote->setProductNewQty($qtys[$product->getId()], $product->getId());
                }

                $quote->save();

                $saved = true;
            } catch (\Exception $e) {
                $saved = false;
            } catch (Mage_Exception $e) {
                $saved = false;
            }
        }
        return $saved;
    }
}
