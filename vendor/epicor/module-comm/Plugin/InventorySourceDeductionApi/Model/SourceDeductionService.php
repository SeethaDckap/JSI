<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\InventorySourceDeductionApi\Model;

use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/*
 * proceed placed order shipment when
 * product deleted with zero qty and manage qty is false
 * when placed order
 */
class SourceDeductionService
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\InventorySourceDeductionApi\Model\SourceDeductionService $subject
     * @param \Closure $proceed
     * @param SourceDeductionRequestInterface $sourceDeductionRequest
     * @throws LocalizedException
     */
    public function aroundExecute(
        \Magento\InventorySourceDeductionApi\Model\SourceDeductionService $subject,
        \Closure $proceed,
        SourceDeductionRequestInterface $sourceDeductionRequest
    ) {
        try {
            $proceed($sourceDeductionRequest);
        } catch (LocalizedException $e) {
            $this->logger->info(
                "SOU(skipping update source item inventory when zero or less qty found with SKU and product is deleted): " . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
