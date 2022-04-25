<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Adminhtml\Arpayments;

use Magento\Backend\App\Action;

class View extends \Epicor\Customerconnect\Controller\Adminhtml\Arpayments
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Epicor_Customerconnect::actions_view';

    /**
     * View order detail
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $arpayment = $this->_initArpayment();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($arpayment) {
            try {
                $resultPage = $this->_initAction();
                $resultPage->getConfig()->getTitle()->prepend(__('AR Payments'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addError(__('Exception occurred during AR Payment load'));
                $resultRedirect->setPath('adminhtml/arpayments/index');
                return $resultRedirect;
            }
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $arpayment->getIncrementId()));
            return $resultPage;
        }
        $resultRedirect->setPath('adminhtml/arpayments/index');
        return $resultRedirect;
    }
}
