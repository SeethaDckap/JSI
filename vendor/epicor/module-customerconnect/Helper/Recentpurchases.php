<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Helper;


class Recentpurchases extends \Epicor\Customerconnect\Helper\Data
{
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuod
     */
    protected $customerconnectMessageRequestCuod;

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */
    protected $commonLocaleFormatDateHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuod
     */
    protected $cuodResponse;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;
    /**
     * @var \Epicor\Common\Helper\Cart
     */
    private $commonCartHelper;


    /**
     * Recentpurchases constructor.
     * @param \Epicor\Comm\Helper\Messaging\Context $context
     * @param \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper
     * @param \Epicor\Customerconnect\Model\Message\Request\Cuod $customerconnectMessageRequestCuod
     * @param \Epicor\Common\Helper\Cart $commonCartHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuod $customerconnectMessageRequestCuod,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Comm\Helper\Locations $commLocationsHelper
    )
    {
        $this->messageManager = $context->getMessageManager();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $commonLocaleFormatDateHelper, $customerconnectMessageRequestCuod);
        $this->commonCartHelper = $commonCartHelper;
        $this->commLocationsHelper = $commLocationsHelper;
    }

    /**
     * Returns a recentpurchases reorder url from the recentpurchases object provided,
     *
     * Also optional to change the return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $recentpurchasesObj
     * @param string $return
     * @return type
     */
    public function getRecentpurchasesReorderUrl($recentpurchasesObj, $return = 'customerconnect/recentpurchases/')
    {
        $recentpurchaseDetails = json_encode($recentpurchasesObj->getData());
        $params = array(
            'recentpurchaseitem' => $this->urlEncoder->encode($recentpurchaseDetails),
            'return' => $this->urlEncoder->encode($return)
        );

        return $this->_getUrl('customerconnect/recentpurchases/reorder', $params);
    }

    /**
     * process single item from recent purchases gride
     * @return mixed
     */
    public function processSingleItem($recentpurchasesItem, $orderQtyArray = false)
    {
        $recentpurchaseDetails = json_decode($recentpurchasesItem, true);
        if ($orderQtyArray) {
            $orderQtyArray = json_decode(base64_decode($orderQtyArray), true);
            $itemRow = $recentpurchaseDetails['product_code'] . $recentpurchaseDetails['last_order_number'] . $recentpurchaseDetails['unit_of_measure_code'];
            if (isset($orderQtyArray[$itemRow])) {
                $recentpurchaseDetails['total_qty_ordered'] = $orderQtyArray[$itemRow];
            }
        }
        $order = $this->getOrderDetails($recentpurchaseDetails['last_order_number']);

        return $this->reduceLines($order, $recentpurchaseDetails);
    }

    /**
     *  Perform massaction processing
     * @param $itemsPerOrder
     * @return $order object
     */
    public function processMassaction($postData)
    {
        $itemsPerOrder = [];
        foreach ($postData as $key => $postrow) {
            if (strpos($key, 'massactionXYYX') > -1) {
                $recentpurchaseDetails = json_decode(base64_decode($postrow), true);
                $itemsPerOrder[$recentpurchaseDetails['last_order_number']][$recentpurchaseDetails['product_code'] . $recentpurchaseDetails['unit_of_measure']]
                    = $recentpurchaseDetails['total_qty_ordered'];
            }
        }
        $refactoredArray = $this->refactorLineArray($itemsPerOrder);

        //add merged lineArray to order structure (doesn't actually matter which order it is)
        $order = $refactoredArray['order'];
        $order->getLines()->setLine($refactoredArray['lineArray']);
        return $order;
    }

    /**
     *  Get order details for recentpurchases
     * @param $recentpurchaseDetails
     */
    public function getOrderDetails($lastOrderNumber)
    {
        if ($lastOrderNumber) {

            $order = isset($this->cuodResponse[$lastOrderNumber]) ? $this->cuodResponse[$lastOrderNumber] : '';
            if ($order) {
                $order->getLines()->setLine($this->cuodResponse[$lastOrderNumber]['original_lines']);
            } else {
                // send a CUOD message to retrieve order details in xml form
                $result = $this->sendOrderRequest(
                    $this->getErpAccountNumber(),
                    $lastOrderNumber,
                    $this->getLanguageMapping($this->localeResolver->getLocale())
                );
                $order = isset($result['order']) ? $result['order'] : '';
                $this->cuodResponse[$lastOrderNumber] = $order;

                // dont' save if order not returned or there are no lines returned on the order for some reason
                if ($order && $order->getLines()) {
                    $this->cuodResponse[$lastOrderNumber]['original_lines'] = $order->getLines()->getLine();
                }

            }
            return $order;
        }
    }

    /**
     * @param $order
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     */
    public function processReorder($order)
    {
        if (empty($order) || !$this->commonCartHelper->processReorder($order)) {
            if (!$this->messageManager->getMessages()->getItems()) {
                $this->messageManager->addErrorMessage(__('Failed to build cart for Re-Order request'));
            }
            return 'error';
        }
    }

    /**
     * Reduce the lines in the order returned from the CUOD to only those required
     * @param $order
     * @param $recentpurchaseDetails
     */
    private function reduceLines($order, $recentpurchaseDetails)
    {
        if ($order) {
            $lines = $order->getLines();
            if ($lines) {
                $lineArray = $this->getLineArray($lines);
                foreach ($lineArray as $key => $value) {
                    if ($value->getProductCode() == $recentpurchaseDetails['product_code']
                        && $value->getUnitOfMeasureDescription() == $recentpurchaseDetails['unit_of_measure_code']) {
                        $lineArray[$key]->getQuantity()->setOrdered($recentpurchaseDetails['total_qty_ordered']);
                    } else {
                        unset($lineArray[$key]);
                    }
                }
                $order->getLines()->setLine($lineArray);
            }
        }
        return $order;
    }

    /**
     * Reduce the lines in the massaction order returned from the CUOD to only those required
     * @param $order
     * @param $itemsPerOrder
     * @param $lastOrderNumber
     * @return array $lineArray
     */
    private function reduceLinesMassaction($order, $itemsPerOrder, $lastOrderNumber)
    {
        if ($order) {
            $lines = $order->getLines();
            if ($lines) {
                $lineArray = $this->getLineArray($lines);
                foreach ($lineArray as $key => $value) {
                    $productCodeAndUom = $value->getProductCode() . $value->getUnitOfMeasureDescription();
                    if (in_array($productCodeAndUom, array_keys($itemsPerOrder[$lastOrderNumber]))) {
                        $lineArray[$key]->getQuantity()->setOrdered(
                            $itemsPerOrder[$lastOrderNumber][$productCodeAndUom]);
                    } else {
                        unset($lineArray[$key]);
                    }
                }
            }
        }
        return $lineArray;
    }

    /**
     * @param array $itemsPerOrder
     * @return array
     */
    private function refactorLineArray($itemsPerOrder)
    {
        $lineArray = [];
        $productsInLineArray = [];
        foreach ($itemsPerOrder as $key => $items) {
            $order = $this->getOrderDetails($key);
            $requiredLines = $this->reduceLinesMassaction($order, $itemsPerOrder, $key);
            foreach ($requiredLines as $requiredLine) {
                $locationProductCode = $requiredLine->getProductCode() . $requiredLine->getUnitOfMeasureDescription();
                //if locations enabled, take into account
                if ($this->commLocationsHelper->isLocationsEnabled()) {
                    $locationProductCode .= $requiredLine->getLocationCode();
                }

                //if already processed add qty to it
                if (isset($productsInLineArray[$locationProductCode])) {
                    $processedLine = $productsInLineArray[$locationProductCode];
                    $existingQty = $processedLine->getQuantity()->getOrdered();
                    $newQty = $requiredLine->getQuantity()->getOrdered();
                    $requiredLine->getQuantity()->setOrdered($existingQty + $newQty);
                    unset($productsInLineArray[$locationProductCode]);
                }
                $lineArray[$locationProductCode] = $requiredLine;
                $productsInLineArray[$locationProductCode] = $requiredLine;
            }
        }
        return ['lineArray' => $lineArray, 'order' => $order];
    }

    /**
     * return lines from order in array format
     * @param array|object $lines
     * @return array
     */
    private function getLineArray($lines)
    {
        $line = $lines->getLine();
        // when only one item is returned from the order, it isn't an array, convert for further processing
        if (is_array($line)) {
            $lineArray = $line;
        } else {
            $lineArray[] = $line;
        }
        return $lineArray;
    }

}