<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Epicor\Elasticsearch\Api\QuickSearchResponseBuilderInterface;

/**
 * ECC Suggest ajax controller
 */
class Suggest extends Action
{

    /**
     * @var QuickSearchResponseBuilderInterface
     */
    private $responseBuilderInterface;

    /**
     * Suggest constructor.
     * @param Context $context
     * @param QuickSearchResponseBuilderInterface $responseBuilderInterface
     */
    public function __construct(
        Context $context,
        QuickSearchResponseBuilderInterface $responseBuilderInterface
    ) {
        $this->responseBuilderInterface  = $responseBuilderInterface;
        parent::__construct($context);
    }

    /**
     * Retrieve quick search result in json format
     *
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }
        $responseData = [];
        $responseData['result'] = $this->responseBuilderInterface->buildQuickSearchResponse();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
}