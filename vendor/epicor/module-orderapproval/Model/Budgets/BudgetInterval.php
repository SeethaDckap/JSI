<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use DateTime;
use Epicor\OrderApproval\Logger\Logger;
use InvalidArgumentException;
use Magento\Framework\DataObject;

class BudgetInterval extends DataObject
{
    /**
     * @var DateTime
     */
    private $startInterval;

    /**
     * @var DateTime
     */
    private $endInterval;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * BudgetInterval constructor.
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Logger $logger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->logger = $logger;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     */
    public function setBudgetInterval(DateTime $startDate, DateTime $endDate)
    {
        try {
            $startInterval = $startDate;
            $endInterval = $endDate;
            if ($startInterval >= $endInterval) {
                throw new InvalidArgumentException('Invalid interval, can not set Interval');
            }
            $this->setStartInterval($startInterval);
            $this->setEndInterval($endInterval);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return DateTime
     */
    public function getIntervalStart()
    {
        return $this->startInterval;
    }

    /**
     * @return DateTime
     */
    public function getIntervalEnd()
    {
        return $this->endInterval;
    }

    /**
     * @param DateTime $dateTime
     */
    private function setStartInterval(DateTime $dateTime)
    {
        $this->startInterval = $dateTime;
    }

    /**
     * @param DateTime $endInterval
     */
    private function setEndInterval(DateTime $endInterval)
    {
        $this->endInterval = $endInterval;
    }
}