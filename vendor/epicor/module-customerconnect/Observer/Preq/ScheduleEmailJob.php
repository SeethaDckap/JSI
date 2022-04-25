<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Preq;

class ScheduleEmailJob implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Cron\Model\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    public function __construct(
        \Magento\Cron\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
    }


    /**
     * schedule job for proceesing PREQ email action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Customerconnect_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $id = $observer->getEvent()->getEntityId();
        $currentTime = $this->dateTime->gmtTimestamp();
        $createdAt = strftime('%Y-%m-%d %H:%M:%S', $currentTime + 15);
        $scheduledAt = strftime('%Y-%m-%d %H:%M:%S', $currentTime + 20);
        $schedule = $this->scheduleFactory->create()
            ->setCronExpr("* * * * *")
            ->setJobCode("preq_queue_process")
            ->setStatus("pending")
            ->setCreatedAt($createdAt)
            ->setScheduledAt($scheduledAt)
            ->setMessages("ID=".$id);

        $schedule->save();

        return $this;
    }

}