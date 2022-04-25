<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Ui\Component\Listing\Column;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderComment extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    private $_orderService;

    private $_orderRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Sales\Model\Service\OrderService $orderService,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = [])
    {
        $this->_orderService = $orderService;
        $this->_orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order  = $this->_orderRepository->get($item["entity_id"]);
                $item[$this->getData('name')] = $order->getData('customer_note');
                if(empty($order->getData('customer_note'))) {
                    $comment = $this->_orderService->getCommentsList($item["entity_id"]);
                    $emptyCommentsCheck = $comment->getFirstItem()->getData('comment');
                    if(!preg_match("/(capture|authorize)/i", $emptyCommentsCheck)){
                        $item[$this->getData('name')] =  $emptyCommentsCheck;
                    }
                }
            }
        }
        return $dataSource;
    }
}