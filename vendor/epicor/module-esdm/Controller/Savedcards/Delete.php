<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Controller\Savedcards;


class Delete extends \Epicor\AccessRight\Controller\Action
{

     protected $_gridFactory; 

     protected $_session;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;     


     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Esdm\Model\TokenFactory $gridFactory,
        \Magento\Customer\Model\Session $customerSession
     ) {
        $this->_gridFactory = $gridFactory;
        $this->_session = $customerSession;
        parent::__construct($context);
    }

  public function execute()
  {
        if (!$this->_session->isLoggedIn()) {
            $this->messageManager->addErrorMessage('Session expired please login');
            return;
        }
        if (!$this->_isAccessAllowed("Epicor_Customer::my_account_esdm_delete")) {
            $this->messageManager->addErrorMessage('You do not have permissions to delete.');
            $this->_redirect('esdm/savedcards');
            return;
        }
        $tokenId = $this->getRequest()->get('card_id');
        if ($tokenId) {
            try {
                $card = $this->_gridFactory->create();
                $card->load($tokenId);
                /* @var $card Epicor_Esdm_Model_Token */
                $customerId = $this->_session->getCustomer()->getId();
                if ($customerId != $card->getCustomerId()) {
                    throw new \Exception('Invalid customer');
                }
                $card->setReuseable(false);
                $card->save();
                $this->messageManager->addSuccessMessage("Card has been removed");
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage("An error has occurred while removing your saved card");
            }
        }
        $this->_redirect('esdm/savedcards');
  }
  
}