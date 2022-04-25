<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Controller\Form;


use Magento\Framework\App\ResponseInterface;

class Order extends \Epicor\QuickOrderPad\Controller\Form
{
    /**
     * @var \Epicor\QuickOrderPad\Model\ColumnSort
     */
    private $columnSort;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\QuickOrderPad\Model\ColumnSort $columnSort = null
    ){
        parent::__construct($context, $resultPageFactory, $resultLayoutFactory);

        $this->columnSort = $columnSort;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $responseData = [];
        $responseData['url'] = $this->getQuickOrderPadUrl();

        $response = json_encode($responseData);
        $this->getResponse()->setBody($response);
    }

    /**
     * @return string
     */
    private function getQuickOrderPadUrl()
    {
        $params = $this->columnSort->getDefaultSortByParams();

        return $this->columnSort->getBaseUrl() .
            'quickorderpad/form?' . $this->getSortByParam($params) . '&' .  $this->getDirParam($params);
    }

    /**
     * @param $params
     * @return string
     */
    private function getSortByParam($params)
    {
        $sortBy = $params['sort_by'] ?? '';
        if($sortBy){
            return 'sort_by=' . $sortBy;
        }

        return '';
    }

    /**
     * @param $params
     * @return string
     */
    private function getDirParam($params)
    {
        $dir = $params['dir'] ?? '';
        if($dir){
            return 'dir=' . $dir;
        }

        return '';
    }
}