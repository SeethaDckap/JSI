<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Bsv;

use Magento\Framework\Event\Observer;
use Magento\Framework\Message\Factory as MessageFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Epicor\Comm\Helper\Data as Helper;

/**
 * Class OFourOBsvLine
 * @package Epicor\Comm\Observer\Bsv
 */
class OFourOBsvLine implements ObserverInterface
{
    /**
     * Line Code error indicating Product Not available for purchase
     */
    const LINE_ERROR_CODE = "040";

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * OFourOBsvLine constructor.
     * @param MessageFactory $messageFactory
     * @param ManagerInterface $messageManager
     * @param Helper $helper
     */
    public function __construct(
        MessageFactory $messageFactory,
        ManagerInterface $messageManager,
        Helper $helper
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
    }

    /**
     * Remove line item with 040 code from cart
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $bsv = $observer->getEvent()->getMessage();
        $response = $bsv->getResponse();
        $lines = $this->getLineItems($bsv, $response);

        if (empty($lines) === false) {
            $quote = $bsv->getQuote();

            if ($quote) {
                $lineErrors = $this->getLineItemsWithError($lines);
                if (empty($lineErrors) === false) {
                    $this->clearLineErrors($quote, $lineErrors);
                }
            }
        }

    }

    /**
     * Get Line items from BSV response
     * @param \Epicor\Comm\Model\Message\Request\Bsv $bsv
     * @param array $response
     * @return array
     */
    private function getLineItems($bsv, $response)
    {
        $lines = [];
        if (isset($response['lines'])
            && isset($response['lines']['line'])
            && count($response['lines']['line']) > 0
        ) {
            $lines = $bsv->_getGroupedDataArray('lines', 'line', $response);
        }
        return $lines;
    }

    /**
     * Gets the line items with error code 040
     * @param array $lines
     * @return array
     */
    private function getLineItemsWithError($lines)
    {
        $lineErrors = [];
        $separator = $this->helper->getUOMSeparator();
        foreach ($lines as $line) {
            if (isset($line['status'])
                && isset($line['status']['code'])
                && $line['status']['code'] == self::LINE_ERROR_CODE
            ) {
                $lineErrors[] = $line['productCode'];
                if (isset($line['unitOfMeasureCode'])) {
                    $lineErrors[] = $line['productCode'] . $separator . $line['unitOfMeasureCode'];
                }
                if (isset($line['locationCode'])) {
                    $lineErrors['locationCode'][$line['locationCode']][] = $line['productCode'];
                    if (isset($line['unitOfMeasureCode'])) {
                        $lineErrors['locationCode'][$line['locationCode']][]  = $line['productCode'] . $separator . $line['unitOfMeasureCode'];
                    }
                }
            }
        }
        return $lineErrors;
    }

    /**
     * Remove line item with code 040 from cart
     * @param \Epicor\Comm\Model\Quote $quote
     * @param array $lineErrors
     */
    private function clearLineErrors($quote, $lineErrors)
    {
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $error = false;
            $message = "";
            $sku = $item->getSku();
            $locationCode = $item->getEccLocationCode();

            if (is_null($locationCode) && in_array($sku, $lineErrors)) {
                $message = $sku . " product not available to purchase.";
                $error = true;
            } else if (isset($lineErrors['locationCode'])
                && isset($lineErrors['locationCode'][$locationCode])
                && in_array($sku, $lineErrors['locationCode'][$locationCode])
            ) {
                $message = $sku . " product not available to purchase at ". $locationCode .".";
                $error = true;
            }

            if ($error) {
                $this->addComplexErrorForItem($item->getProductId(), $message);
                $quote->setHasError(true);
                $quote->deleteItem($item);
            }
        }
    }

    /**
     * Set error message for line items
     * @param int $productId
     * @param string $message
     */
    private function addComplexErrorForItem($productId, $message)
    {
        $this->messageManager->addComplexErrorMessage(
            'bsvLineCodeError',
            [
                'product_id' => $productId,
                'message'    => $message
            ]
        );
    }
}