<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount\Currency;

use \Epicor\Comm\Model\Customer\Erpaccount\CurrencyFactory as CurrencyFactory;

class Save extends \Magento\Backend\App\Action
{
    private $gridFactory;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param CurrencyFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CurrencyFactory $gridFactory
    ) {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('currency/epicorcomm_customer_erpaccount_currency/edit');
            return;
        }
        try {
            $rowData = $this->gridFactory->create();
            $itemData = $rowData->load($data['id']);
            $erpAccountId = $itemData->getErpAccountId();

            $itemData->setData('min_order_amount',$data['min_order_amount']);

            $itemData->save();
            $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('adminhtml/epicorcomm_customer_erpaccount/edit',['id' => $erpAccountId]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Epicor_Com::save');
    }
}