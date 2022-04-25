<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class NewAction extends \Epicor\Lists\Controller\Lists
{

    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_create';
    public function execute()
    {
        $this->_forward('edit');
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->isDispatched() && $request->getActionName() !== 'denied' && !$this->_isAllowed()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getUpdate()->addHandle('frontend_denied_account');
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            return $resultPage;

        }

        return parent::dispatch($request);
    }
}
