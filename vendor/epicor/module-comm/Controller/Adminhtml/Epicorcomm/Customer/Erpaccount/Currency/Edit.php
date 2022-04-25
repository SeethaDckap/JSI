<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount\Currency;

use Magento\Framework\Controller\ResultFactory;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\CollectionFactory as CurrencyFactory;

class Edit extends \Magento\Backend\App\Action
{
    private $coreRegistry;
    private $gridFactory;

    /**
     * EditRow constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->gridFactory = $currencyFactory;

    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int)$this->getRequest()->getParam('id');
        $rowData = $this->gridFactory->create();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($rowId) {
            $rowData->addFieldToFilter('id', ['eq' => $rowId]);
            $rowData = $rowData->getFirstItem();
            $rowTitle = $rowData->getTitle();
            if (!$rowData->getId()) {
                $this->messageManager->addError(__('row data no longer exist.'));

                $this->_redirect('adminhtml/epicorcomm_customer_erpaccount');
                return;
            }
        }

        $this->coreRegistry->register('row_data', $rowData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $rowId ? __('Edit Currency Min Order Amount ') . $rowTitle : __('Edit Currency');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Epicor_Comm::add_row');
    }
}