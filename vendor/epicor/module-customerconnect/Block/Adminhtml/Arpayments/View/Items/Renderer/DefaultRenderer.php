<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Arpayments\View\Items\Renderer;

use Epicor\Customerconnect\Model\ArPayment\Order\Item;

/**
 * Adminhtml sales order item renderer
 *
 * @api
 * @since 100.0.2
 */
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
    /**
     * Json helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;      
    
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $messageHelper, $checkoutHelper, $data);
    }
    
    /**
     * @param \Magento\Framework\DataObject|Item $item
     * @param string $column
     * @param null $field
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 100.1.0
     */
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $info = $this->jsonHelper->jsonDecode($item->getAdditionalData());
        $html = '';
        switch ($column) {
            case 'invoiceAmount':
            case 'invoiceBalance':
            case 'settlementTermAmount':
                if (isset($info[$column])) {
                    $price = $info[$column]; 
                    $html = $this->displayPrices($price, $price);
                }
                break;
            case 'dispute':
                if (isset($info[$column]) && isset($info['disputeComment'])) {
                    $html = "<strong>Dispute: </strong>";
                    $html .= ($info[$column]) ? "True" : 'No';
                    $html .= "<br>";
                    $html .= "<strong>Dispute Comments: </strong>";
                    $html .= ($info['disputeComment']) ?  $info['disputeComment'] : '';
                }
                break;
            case 'paymentAmount':
                $basePrice = $item->getBaseRowTotal();
                $price = $item->getRowTotal();
                $html = $this->displayPrices($basePrice, $price);
                break;
            case 'invoiceDate':
            case 'dueDate':
                if (isset($info[$column])) {
                    $html = $this->processDate($info[$column]);
                }
                break;
            default:
                if (isset($info[$column])) {
                    $html = $info[$column];
                }
        }
        return $html;
    }
    
    /**
     * 
     * Get processed date
     * @param string
     * @return string
     */
    public function processDate($rawDate=NULL)
    {
        if ($rawDate) {
            $timePart = substr($rawDate, strpos($rawDate, "T") + 1);
            if (strpos($timePart, "00:00:00") !== false) {
                $processedDate = $this->customerconnectHelper->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            } else {
                $processedDate = $this->customerconnectHelper->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            }
        } else {
            $processedDate = '';
        }
        return $processedDate;
    }     
}
