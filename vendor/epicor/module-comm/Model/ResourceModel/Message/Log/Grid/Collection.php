<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Model\ResourceModel\Message\Log\Grid;


use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\App\Response\RedirectInterface;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirectInterface;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        OrderRepository $orderRepository,
        \Magento\Framework\App\RequestInterface $request,
        RedirectInterface $redirectInterface,
        $mainTable = 'ecc_message_log',
        $resourceModel = '\Epicor\Comm\Model\ResourceModel\Message\Log'
    ) {
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->redirectInterface = $redirectInterface;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $orderId = $this->request->getParam('order_id');
        $refererUrl = $this->redirectInterface->getRefererUrl();
        $quoteId = null;
        if($orderId){
            $quoteId = $this->orderRepository->get($orderId)->getQuoteId();
        }else if($refererUrl){
            $orderPath = explode("/",parse_url($refererUrl, PHP_URL_PATH));
            $orderId = $orderPath[6];
            $quoteId = $this->orderRepository->get($orderId)->getQuoteId();
        }

        $this->getSelect()
            ->where('message_category=?', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_ORDER)
            ->where("message_secondary_subject REGEXP '{$this->getRegex($quoteId)}'");
        return $this;
    }

    private function getRegex($quoteId)
    {
        return 'Basket Quote ID:[[:space:]]' . $quoteId . '[^0-9]';
    }
}
