<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\Order;

class Info
{
    /**
     * Retrieve current order model instance
     * @param \Magento\Sales\Block\Order\Info $subject
     * @param array $result
     * @return \Magento\Sales\Model\Order
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOrder(\Magento\Sales\Block\Order\Info $subject, $result)
    {
        if (!$result->getCustomerNoteAdded()) {
            $result->setShippingDescription($result->getShippingDescription() . '<br/>' . $result->getCustomerNote());
            $result->setCustomerNoteAdded(true);
        }

        return $result;
    }

    /**
     * Escape HTML entities
     * @param \Magento\Sales\Block\Order\Info $subject
     * @param string|array $data
     * @param array|null $allowedTags
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEscapeHtml(\Magento\Sales\Block\Order\Info $subject, $data, $allowedTags = null)
    {
        return [$data, ['br']];
    }
}
