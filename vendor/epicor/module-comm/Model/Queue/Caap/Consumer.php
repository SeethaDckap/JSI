<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Queue\Caap;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Notification\NotifierInterface;
use Epicor\Customerconnect\Model\ArPayment\OrderFactory;
use Epicor\Customerconnect\Model\ArPayment\QuoteFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Epicor\Comm\Api\Data\CaapInfoInterface;
/**
 * Consumer For CAAP Message Use For Sync.
 * Call Via Magento Queue Mechanism.
 */
class Consumer
{

    /**
     * NotifierInterface.
     *
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * File System.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Quote Factory.
     *
     * @var QuoteFactory
     */
    private $quoteRepository;

    /**
     * Order Factory.
     *
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * Event Manager.
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Registry.
     *
     * @var Registry
     */
    private $registry;


    /**
     * Consumer constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger          LoggerInterface.
     * @param Filesystem               $filesystem      Filesystem.
     * @param NotifierInterface        $notifier        NotifierInterface.
     * @param OrderFactory             $orderFactory    OrderFactory.
     * @param QuoteFactory             $quoteRepository QuoteFactory.
     * @param ManagerInterface         $eventManager    ManagerInterface.
     * @param Registry                 $registry        Registry.
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Filesystem $filesystem,
        NotifierInterface $notifier,
        OrderFactory $orderFactory,
        QuoteFactory $quoteRepository,
        ManagerInterface $eventManager,
        Registry $registry
    ) {
        $this->logger            = $logger;
        $this->filesystem        = $filesystem;
        $this->notifier          = $notifier;
        $this->orderFactory      = $orderFactory;
        $this->quoteRepository   = $quoteRepository;
        $this->eventManager      = $eventManager;
        $this->registry          = $registry;
    }

    /**
     * Consumer Logic.
     *
     * @param CaapInfoInterface $caapInfo CaapInfoInterface.
     *
     * @return void
     */
    public function process(CaapInfoInterface $caapInfo)
    {
        try {
            $arOrderId = $caapInfo->getOrderId();
            if($arOrderId) {
                /** @var  $order \Epicor\Customerconnect\Model\ArPayment\Order */
                $order = $this->orderFactory->create()->load($arOrderId);
                if ($order->isObjectNew() === false) {
                    if (!$this->registry->registry("offline_arpaymentorders_{$order->getId()}")) {
                        $this->registry->register("offline_arpaymentorders_{$order->getId()}", true);
                    }

                    $quotes = $this->quoteRepository->create();
                    $quote  = $quotes->load($order->getQuoteId());
                    $this->eventManager->dispatch(
                        'ar_checkout_submit_all_after',
                        [
                            'quote' => $quote,
                            'order' => $order,
                        ]
                    );
                    $this->registry->unregister("offline_arpaymentorders_{$order->getId()}");
                }//end if
            }
        } catch (LocalizedException $e) {
            $this->notifier->addCritical(
                __('Error during Caap background order process occurred'),
                __('Error during Caap background order process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while Caap background order process. ' . $e->getMessage());
            $this->logger->error($e);
        } catch (\Exception $e) {
            $this->notifier->addCritical(
                __('Error during Caap background order process occurred'),
                __('Error during Caap background order process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while Caap background order process. ' . $e->getMessage());
            $this->logger->error($e);
        }//end try

    }//end process()


}
