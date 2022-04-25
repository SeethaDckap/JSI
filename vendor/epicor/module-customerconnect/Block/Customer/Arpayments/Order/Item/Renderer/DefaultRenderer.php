<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace  Epicor\Customerconnect\Block\Customer\Arpayments\Order\Item\Renderer;

use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Order item render block
 */
class DefaultRenderer extends \Magento\Framework\View\Element\Template
{
    
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;  

    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct($context, $data);
    }
    
     /**
     * @param \Magento\Framework\DataObject $item
     * @return $this
     */
    public function setItem(\Magento\Framework\DataObject $item)
    {
        $this->setData('item', $item);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getItem()
    {
        return $this->_getData('item');
    }
    
    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

   /**
     * Return sku of order item.
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getItem()->getSku();
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
