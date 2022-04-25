<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Epicor\Elasticsearch\Model\ResourceModel\Boost\CollectionFactory;

/**
 * Boost Adminhtml MassInactive controller.
 *
 */
class MassInactive extends Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try
        {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $deactivateRecords = 0;
            foreach ($collection as $item)
            {
                $from_date = $item->getData('from_date');
                $to_date = $item->getData('to_date');
                $item->setIsActive(false);
                if(!is_null($from_date)){
                    $item->setFromDate(\DateTime::createFromFormat('Y-m-d', $from_date)->format('m/d/Y'));
                }
                if(!is_null($to_date)){
                    $item->setToDate(\DateTime::createFromFormat('Y-m-d', $to_date)->format('m/d/Y'));
                }
                $item->save();
                ++$deactivateRecords;
            }
            if ($deactivateRecords)
            {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were inactivated.', $deactivateRecords));
            }
        }
        catch (\Exception $e)
        {
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Check if allowed to manage boost.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Epicor_Elasticsearch::boost');
    }
}
