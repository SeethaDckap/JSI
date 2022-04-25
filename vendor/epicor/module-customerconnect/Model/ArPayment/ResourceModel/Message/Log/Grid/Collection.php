<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Message\Log\Grid;


use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Epicor\Customerconnect\Model\ArpaymentsRepository;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var ArpaymentRepository
     */
    protected $arpaymentRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Magento\Backend\Model\Session $session,
        ArpaymentsRepository $arpaymentRepository,
        \Magento\Framework\App\RequestInterface $request,
        $mainTable = 'ecc_message_log',
        $resourceModel = '\Epicor\Comm\Model\ResourceModel\Message\Log'
    )
    {
        $this->session = $session;
        $this->arpaymentRepository = $arpaymentRepository;
        $this->request = $request;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $arpaymentId = $this->request->getParam('arpayment_id');
        $quoteId = null;
        if($arpaymentId){
            $quoteId = $this->arpaymentRepository->get($arpaymentId)->getQuoteId();
            $this->session->setMessageLogArpaymentId($arpaymentId);
        }else if($this->session->getMessageLogArpaymentId()){
            $arpaymentId = $this->session->getMessageLogArpaymentId();
            $quoteId = $this->arpaymentRepository->get($arpaymentId)->getQuoteId();
            $this->session->unsetData('message_log_arpayment_id');
        }

        $this->getSelect()
            ->where('message_category=?', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_ORDER)
            ->where('message_secondary_subject like ?', '%ARPAYMENTS Quote ID: ' . $quoteId . '%');
        return $this;
    }
}